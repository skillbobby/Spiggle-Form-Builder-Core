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
