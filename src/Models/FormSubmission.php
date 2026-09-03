<?php

namespace Spiggle\FormBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Database\Factories\FormSubmissionFactory;
use Spiggle\FormBuilder\Events\FormSubmitted;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\OptionColor;

class FormSubmission extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'form_id',
        'status',
        'data',
        'ip_address',
        'user_agent',
        'user_id',
        'meta',
        'archived_at',
    ];

    protected $casts = [
        'data' => 'array',
        'meta' => 'array',
        'archived_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('form-builder.tables.submissions', 'form_builder_submissions');
    }

    protected static function newFactory(): FormSubmissionFactory
    {
        return FormSubmissionFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            $submission->uuid = $submission->uuid ?: (string) Str::uuid();
            $submission->status = $submission->status ?: 'new';
        });

        static::created(function (self $submission): void {
            FormSubmitted::dispatch($submission);
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'user_id');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function archive(): void
    {
        $this->status = 'archived';
        $this->archived_at = now();
        $this->save();
    }

    /**
     * Human-readable values using option labels from the form schema.
     *
     * @return array<string, mixed>
     */
    public function displayData(): array
    {
        $form = $this->form;
        $data = $this->data ?? [];
        $out = [];

        if (! $form) {
            return $data;
        }

        foreach ($form->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }

            $value = $data[$name] ?? null;
            $label = $field['label_override'] ?: ($field['label'] ?? $name);

            if (FieldCatalog::requiresOptions($field['type'] ?? 'text')) {
                $map = collect($field['options'] ?? [])->pluck('label', 'value')->all();
                if (is_array($value)) {
                    $value = collect($value)->map(fn ($v) => $map[(string) $v] ?? $v)->all();
                } elseif ($value !== null) {
                    $value = $map[(string) $value] ?? $value;
                }
            }

            $out[$label] = $value;
        }

        return $out;
    }

    /**
     * Admin infolist HTML for answers (inline styles; Filament v5 has no Tailwind utilities).
     */
    public function answersHtml(): string
    {
        $form = $this->form;
        $data = $this->data ?? [];

        if (! $form) {
            return '<p style="opacity:.7">No form schema is attached to this submission.</p>';
        }

        $html = '<div style="display:flex;flex-direction:column;gap:18px">';

        foreach ($form->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');
            $label = (string) ($field['label_override'] ?: ($field['label'] ?? $name));
            $value = $data[$name] ?? null;
            $html .= '<div style="border-bottom:1px solid color-mix(in srgb, currentColor 12%, transparent);padding-bottom:12px">';
            $html .= '<div style="font-size:11px;letter-spacing:.04em;text-transform:uppercase;opacity:.55;margin-bottom:6px">'.e($label).'</div>';
            $html .= $this->formatAnswerHtml($field, $value, $type);
            $html .= '</div>';
        }

        return $html.'</div>';
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function formatAnswerHtml(array $field, mixed $value, string $type): string
    {
        if ($value === null || $value === '' || $value === []) {
            return '<div style="opacity:.45">—</div>';
        }

        if (FieldCatalog::isBoolean($type)) {
            return '<div>'.(! empty($value) ? 'Yes' : 'No').'</div>';
        }

        if (FieldCatalog::requiresOptions($type)) {
            /** @var list<array<string, mixed>> $options */
            $options = is_array($field['options'] ?? null) ? $field['options'] : [];
            $map = collect($options)->pluck('label', 'value')->all();
            $items = is_array($value) ? $value : [$value];

            if (in_array($type, ['tags', 'multi_select', 'select', 'radio'], true) || is_array($value)) {
                $badges = '';
                foreach ($items as $raw) {
                    $rawStr = (string) $raw;
                    $itemLabel = (string) ($map[$rawStr] ?? $raw);
                    $color = OptionColor::forValue($options, $rawStr);
                    $style = 'display:inline-block;margin:0 6px 6px 0;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600';
                    if ($color) {
                        $style .= ';background:color-mix(in srgb, '.$color.' 18%, #fff);border:1px solid color-mix(in srgb, '.$color.' 45%, #fff);color:'.$color;
                    } else {
                        $style .= ';background:color-mix(in srgb, currentColor 10%, transparent)';
                    }
                    $badges .= '<span style="'.$style.'">'.e($itemLabel).'</span>';
                }

                return $badges !== '' ? $badges : '<div style="opacity:.45">—</div>';
            }

            $first = (string) ($items[0] ?? '');

            return '<div>'.e((string) ($map[$first] ?? $first)).'</div>';
        }

        if (FieldCatalog::isFile($type) && is_array($value)) {
            $parts = '';
            foreach ($value as $file) {
                if (! is_array($file)) {
                    continue;
                }
                $name = e((string) ($file['name'] ?? basename((string) ($file['path'] ?? 'file'))));
                $url = $file['url'] ?? null;
                $parts .= $url
                    ? '<div><a href="'.e((string) $url).'" target="_blank" rel="noopener">'.$name.'</a></div>'
                    : '<div>'.$name.'</div>';
            }

            return $parts !== '' ? $parts : '<div style="opacity:.45">—</div>';
        }

        if ($type === 'url' && is_string($value)) {
            $href = e($value);

            return '<div><a href="'.$href.'" target="_blank" rel="noopener">'.$href.'</a></div>';
        }

        if ($type === 'email' && is_string($value)) {
            $email = e($value);

            return '<div><a href="mailto:'.$email.'">'.$email.'</a></div>';
        }

        if ($type === 'textarea' && ! empty($field['meta']['use_editor']) && is_string($value)) {
            $safe = strip_tags($value, '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><blockquote>');

            return '<div style="line-height:1.5">'.$safe.'</div>';
        }

        if (is_array($value)) {
            $text = collect($value)->map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v))->implode(', ');

            return '<div>'.e($text).'</div>';
        }

        return '<div style="white-space:pre-wrap">'.e((string) $value).'</div>';
    }

    public function excerpt(int $limit = 80): string
    {
        $parts = [];
        foreach ($this->displayData() as $label => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            $text = is_array($value) ? implode(', ', $value) : (string) $value;
            $parts[] = $label.': '.$text;
            if (count($parts) >= 2) {
                break;
            }
        }

        return Str::limit(implode(' · ', $parts), $limit);
    }
}
