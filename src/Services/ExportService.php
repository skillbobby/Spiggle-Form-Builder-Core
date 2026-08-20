<?php

namespace Spiggle\FormBuilder\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spiggle\FormBuilder\Events\SubmissionsExported;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\FeatureCatalog;

class ExportService
{
    /**
     * @param  Builder<FormSubmission>|null  $query
     * @return array{path: string, filename: string, disk: string}
     */
    public function export(string $format, ?Form $form = null, ?Builder $query = null): array
    {
        $query ??= FormSubmission::query()->with('form');
        if ($form) {
            $query->where('form_id', $form->id);
        }

        $submissions = $query->orderBy('id')->get();
        $headers = $this->headers($form, $submissions);
        $rows = $submissions->map(fn (FormSubmission $submission) => $this->row($submission, $headers))->all();

        $format = strtolower($format);

        if (FeatureCatalog::isProExport($format) && ! FeatureCatalog::proUnlocked()) {
            throw new RuntimeException(
                strtoupper($format).' export requires Form Builder Pro. Use CSV or activate a Pro license.'
            );
        }

        $filename = 'submissions-'.($form?->slug ?? 'all').'-'.now()->format('Ymd-His').'.'.$format;
        $disk = (string) config('form-builder.exports.disk', 'local');
        $directory = trim((string) config('form-builder.exports.directory', 'form-builder-exports'), '/');
        $relative = $directory.'/'.$filename;

        Storage::disk($disk)->makeDirectory($directory);

        $contents = match ($format) {
            'xlsx' => $this->toXlsx($headers, $rows),
            'pdf' => $this->toPdf($form, $headers, $rows),
            default => $this->toCsv($headers, $rows),
        };

        Storage::disk($disk)->put($relative, $contents);

        app(AuditLogger::class)->log('export', $form, [
            'format' => $format,
            'filename' => $filename,
            'count' => count($rows),
        ]);

        SubmissionsExported::dispatch($form, $format, $relative, ['count' => count($rows)]);

        return [
            'path' => $relative,
            'filename' => $filename,
            'disk' => $disk,
            'absolute' => Storage::disk($disk)->path($relative),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FormSubmission>  $submissions
     * @return array<int, string>
     */
    protected function headers(?Form $form, $submissions): array
    {
        $headers = ['id', 'uuid', 'form', 'status', 'created_at'];

        $fields = $form?->fields() ?? [];
        if ($fields === []) {
            $names = [];
            foreach ($submissions as $submission) {
                foreach (array_keys($submission->data ?? []) as $key) {
                    $names[$key] = true;
                }
            }
            $headers = array_merge($headers, array_keys($names));
        } else {
            foreach ($fields as $field) {
                $headers[] = (string) ($field['name'] ?? '');
            }
        }

        return array_values(array_filter($headers));
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, mixed>
     */
    protected function row(FormSubmission $submission, array $headers): array
    {
        $data = $submission->data ?? [];
        $row = [];

        foreach ($headers as $header) {
            $row[$header] = match ($header) {
                'id' => $submission->id,
                'uuid' => $submission->uuid,
                'form' => $submission->form?->name,
                'status' => $submission->status,
                'created_at' => optional($submission->created_at)?->toDateTimeString(),
                default => $this->scalar($data[$header] ?? null),
            };
        }

        return $row;
    }

    protected function scalar(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            if (isset($value[0]['name']) || isset($value[0]['path'])) {
                return collect($value)->map(fn ($f) => is_array($f) ? ($f['name'] ?? $f['path'] ?? '') : $f)->implode('; ');
            }

            return implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value));
        }

        return (string) $value;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function toCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($h) => $row[$h] ?? '', $headers));
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    /**
     * SpreadsheetML that Excel opens as a workbook (no extra composer deps).
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function toXlsx(array $headers, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<Worksheet ss:Name="Submissions"><Table>';

        $xml .= '<Row>';
        foreach ($headers as $header) {
            $xml .= '<Cell><Data ss:Type="String">'.htmlspecialchars((string) $header, ENT_XML1).'</Data></Cell>';
        }
        $xml .= '</Row>';

        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ($headers as $header) {
                $xml .= '<Cell><Data ss:Type="String">'.htmlspecialchars((string) ($row[$header] ?? ''), ENT_XML1).'</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return $xml;
    }

    /**
     * Minimal text PDF (printable table). Unicode is transliterated to ASCII.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function toPdf(?Form $form, array $headers, array $rows): string
    {
        $title = $form?->name ? 'Submissions: '.$form->name : 'Form submissions';
        $lines = [$title, 'Exported '.now()->toDateTimeString(), str_repeat('-', 72)];
        $lines[] = implode(' | ', $headers);

        foreach ($rows as $row) {
            $lines[] = implode(' | ', array_map(fn ($h) => Str::limit((string) ($row[$h] ?? ''), 24, ''), $headers));
        }

        $text = implode("\n", $lines);
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '?', $text) ?: $text;

        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 9 Tf 40 800 Td 12 TL (".str_replace("\n", ") ' (", $escaped).") ' ET";
        $length = strlen($stream);

        return "%PDF-1.4\n".
            "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n".
            "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n".
            "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n".
            "4 0 obj<< /Length {$length} >>stream\n{$stream}\nendstream\nendobj\n".
            "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n".
            "xref\n0 6\n0000000000 65535 f \n".
            "trailer<< /Size 6 /Root 1 0 R >>\nstartxref\n0\n%%EOF";
    }
}
