<?php

namespace Spiggle\FormBuilder\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\FieldVisibility;
use Spiggle\FormBuilder\Support\Sanitizer;

class SubmissionManager
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $meta
     */
    public function capture(Form $form, array $data, ?Request $request = null, array $meta = []): FormSubmission
    {
        $clean = [];

        foreach ($form->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name || ! FieldVisibility::isVisible($field, $data)) {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');
            $value = $data[$name] ?? null;
            $allowHtml = (bool) data_get($field, 'meta.use_editor', false);

            if (FieldCatalog::isFile($type)) {
                $clean[$name] = $this->storeFiles($form, $name, $value);
                continue;
            }

            $clean[$name] = Sanitizer::value(
                $value,
                $type,
                $allowHtml && ! config('form-builder.submissions.sanitize_html', true) ? true : $allowHtml
            );

            if (config('form-builder.submissions.sanitize_html', true) && $allowHtml && is_string($clean[$name])) {
                $clean[$name] = strip_tags($clean[$name], '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><blockquote>');
            }
        }

        $ip = null;
        if ($request && config('form-builder.submissions.store_ip', true)) {
            $ip = $request->ip();
            if (config('form-builder.submissions.hash_ip', false) && $ip) {
                $ip = hash('sha256', $ip);
            }
        }

        return FormSubmission::query()->create([
            'form_id' => $form->id,
            'status' => 'new',
            'data' => $clean,
            'ip_address' => $ip,
            'user_agent' => ($request && config('form-builder.submissions.store_user_agent', true))
                ? substr((string) $request->userAgent(), 0, 512)
                : null,
            'user_id' => Auth::id(),
            'meta' => $meta,
        ]);
    }

    protected function storeFiles(Form $form, string $name, mixed $value): mixed
    {
        $files = is_array($value) ? $value : ($value ? [$value] : []);
        $stored = [];
        $disk = (string) config('form-builder.files.disk', 'public');
        $directory = trim((string) config('form-builder.files.directory', 'form-submissions'), '/').'/'.$form->id;

        foreach ($files as $file) {
            if ($file instanceof TemporaryUploadedFile) {
                $path = $file->store($directory, $disk);
                $stored[] = [
                    'disk' => $disk,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'url' => Storage::disk($disk)->url($path),
                ];
            } elseif (is_array($file)) {
                $stored[] = $file;
            } elseif (is_string($file) && $file !== '') {
                $stored[] = ['path' => $file, 'name' => basename($file)];
            }
        }

        return $stored;
    }
}
