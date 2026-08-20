<?php

namespace Spiggle\FormBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spiggle\FormBuilder\Database\Factories\FormFactory;
use Spiggle\FormBuilder\Events\FormCreated;
use Spiggle\FormBuilder\Events\FormDeleted;
use Spiggle\FormBuilder\Events\FormUpdated;
use Spiggle\FormBuilder\Support\PathResolver;
use Spiggle\FormBuilder\Support\SchemaNormalizer;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use RuntimeException;

class Form extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'base_path',
        'description',
        'container_type',
        'schema_version',
        'schema',
        'settings',
        'is_published',
        'is_active',
        'success_message',
        'redirect_url',
        'notify_emails',
    ];

    protected $casts = [
        'schema' => 'array',
        'settings' => 'array',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'notify_emails' => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => FormCreated::class,
        'updated' => FormUpdated::class,
        'deleted' => FormDeleted::class,
    ];

    public function getTable(): string
    {
        return config('form-builder.tables.forms', 'form_builder_forms');
    }

    protected static function newFactory(): FormFactory
    {
        return FormFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (self $form): void {
            $form->uuid = $form->uuid ?: (string) Str::uuid();
            $form->schema_version = $form->schema_version ?: (string) config('form-builder.schema_version', '1.0');
            $form->slug = $form->slug ?: Str::slug($form->name);
            $form->base_path = PathResolver::unique($form->base_path ?: $form->slug, $form->id);
            $form->schema = SchemaNormalizer::normalize($form->schema ?? []);
        });

        static::updating(function (self $form): void {
            if ($form->isDirty('base_path') || blank($form->base_path)) {
                $form->base_path = PathResolver::unique((string) ($form->base_path ?: $form->slug), $form->id);
            }
            if ($form->isDirty('schema')) {
                $form->schema = SchemaNormalizer::normalize($form->schema ?? []);
            }
        });
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(FormAuditLog::class, 'form_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('is_active', true);
    }

    public function publicUrl(): string
    {
        return PathResolver::publicUrl($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function document(): array
    {
        return SchemaNormalizer::document($this->toArray());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array
    {
        return SchemaNormalizer::fields($this->schema ?? []);
    }

    public function field(string $name): ?array
    {
        foreach ($this->fields() as $field) {
            if (($field['name'] ?? null) === $name) {
                return $field;
            }
        }

        return null;
    }

    public function labelPosition(): string
    {
        return (string) data_get($this->settings, 'label_position', 'above');
    }

    public function cloneForm(?string $name = null): self
    {
        if (! FeatureCatalog::proUnlocked()) {
            throw new RuntimeException('Form clone requires Form Builder Pro.');
        }

        $clone = $this->replicate(['id', 'uuid', 'slug', 'base_path']);
        $clone->uuid = (string) Str::uuid();
        $clone->name = $name ?: $this->name.' (Copy)';
        $clone->slug = Str::slug($clone->name);
        $clone->base_path = PathResolver::suggest($clone->name);
        $clone->is_published = false;
        $clone->schema = SchemaNormalizer::normalize($this->schema ?? []);
        $clone->save();

        return $clone->fresh();
    }

    /**
     * Import field definitions from Dynamic Fields custom field ids.
     *
     * @param  array<int, int>  $customFieldIds
     */
    public function importCustomFields(array $customFieldIds): self
    {
        if (! FeatureCatalog::proUnlocked()) {
            throw new RuntimeException('Importing from Dynamic Fields requires Form Builder Pro.');
        }

        if (! class_exists(\Spiggle\DynamicFields\Models\CustomField::class)) {
            return $this;
        }

        $fields = \Spiggle\DynamicFields\Models\CustomField::query()
            ->with('options')
            ->whereIn('id', $customFieldIds)
            ->orderBy('sort_order')
            ->get();

        $schema = $this->schema ?? [];
        if ($schema === []) {
            $schema[] = [
                'label' => 'Imported fields',
                'fields' => [],
            ];
        }

        $last = count($schema) - 1;
        foreach ($fields as $field) {
            $schema[$last]['fields'][] = [
                'name' => $field->name,
                'type' => $field->type,
                'label' => $field->label,
                'required' => (bool) $field->is_required,
                'validation_rules' => $field->validation_rules ?? [],
                'placeholder' => data_get($field->meta, 'placeholder'),
                'hint' => data_get($field->meta, 'hint'),
                'column_span' => 12,
                'options' => $field->options->map(fn ($o) => [
                    'label' => $o->label,
                    'value' => $o->value,
                    'color' => $o->color,
                ])->all(),
                'meta' => $field->meta ?? [],
            ];
        }

        $this->schema = SchemaNormalizer::normalize($schema);
        $this->save();

        return $this->fresh();
    }
}
