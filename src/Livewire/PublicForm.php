<?php

namespace Spiggle\FormBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Services\SubmissionManager;
use Spiggle\FormBuilder\Services\ValidationBuilder;
use Spiggle\FormBuilder\Support\ContainerTypes;
use Spiggle\FormBuilder\Support\FeatureCatalog;
use Spiggle\FormBuilder\Support\FieldCatalog;
use Spiggle\FormBuilder\Support\PathResolver;

class PublicForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $formId;

    public array $data = [];

    public int $step = 0;

    public bool $submitted = false;

    public ?string $successMessage = null;

    public ?string $redirectUrl = null;

    public function mount(string $path): void
    {
        $path = trim($path, '/');

        $form = Form::query()->published()
            ->where(function ($q) use ($path): void {
                $q->where('base_path', $path)->orWhere('slug', $path);
            })
            ->firstOrFail();

        $this->formId = $form->id;
        $this->hydrateDraft($form);
    }

    public function form(): Form
    {
        return Form::query()->published()->findOrFail($this->formId);
    }

    public function resolvedLayout(): string
    {
        return ContainerTypes::resolve($this->form()->container_type ?: 'single');
    }

    public function nextStep(): void
    {
        $form = $this->form();
        if (! ContainerTypes::isStepped($this->resolvedLayout())) {
            return;
        }
        $this->normalizeIncoming();
        $this->validate(
            app(ValidationBuilder::class)->rules($form, $this->step),
            [],
            app(ValidationBuilder::class)->attributes($form, $this->step)
        );
        $this->saveDraft($form);
        $max = max(0, count($form->schema ?? []) - 1);
        $this->step = min($this->step + 1, $max);
    }

    public function previousStep(): void
    {
        $this->step = max(0, $this->step - 1);
        $this->saveDraft($this->form());
    }

    public function goToStep(int $step): void
    {
        $form = $this->form();
        $max = max(0, count($form->schema ?? []) - 1);
        $this->step = max(0, min($step, $max));
    }

    public function submit(): void
    {
        $form = $this->form();
        $this->normalizeIncoming();
        $this->validate(
            app(ValidationBuilder::class)->rules($form),
            [],
            app(ValidationBuilder::class)->attributes($form)
        );

        app(SubmissionManager::class)->capture($form, $this->data, request(), [
            'source' => 'public',
            'path' => $form->base_path,
        ]);

        $this->clearDraft($form);
        $this->submitted = true;
        $this->successMessage = $form->success_message ?: 'Thanks — your response has been recorded.';
        $this->redirectUrl = $form->redirect_url;
        $this->data = [];
    }

    public function render(): View
    {
        $form = $this->form();

        return view('form-builder::livewire.public-form', [
            'formModel' => $form,
            'publicUrl' => PathResolver::publicUrl($form),
        ])->layout('form-builder::layouts.public', [
            'title' => $form->name,
        ]);
    }

    protected function normalizeIncoming(): void
    {
        foreach ($this->form()->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }

            if (($field['type'] ?? '') === 'tags') {
                $raw = $this->data[$name.'_raw'] ?? $this->data[$name] ?? null;
                if (is_string($raw)) {
                    $this->data[$name] = array_values(array_filter(array_map('trim', explode(',', $raw))));
                }
            }
        }
    }

    protected function hydrateDraft(Form $form): void
    {
        foreach ($form->fields() as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }
            if (FieldCatalog::isBoolean($field['type'] ?? 'text')) {
                $this->data[$name] = false;
            } elseif (FieldCatalog::storesArray($field['type'] ?? 'text')) {
                $this->data[$name] = [];
            } else {
                $this->data[$name] = null;
            }
        }

        if (! config('form-builder.drafts.enabled', true) || ! FeatureCatalog::proUnlocked()) {
            return;
        }

        $draft = session(config('form-builder.drafts.session_key', 'form_builder_drafts').'.'.$form->uuid);
        if (is_array($draft)) {
            foreach ($draft as $key => $value) {
                if ($value instanceof TemporaryUploadedFile) {
                    continue;
                }
                $this->data[$key] = $value;
            }
        }
    }

    protected function saveDraft(Form $form): void
    {
        if (! config('form-builder.drafts.enabled', true) || ! FeatureCatalog::proUnlocked()) {
            return;
        }

        $payload = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof TemporaryUploadedFile || (is_array($value) && ($value[0] ?? null) instanceof TemporaryUploadedFile)) {
                continue;
            }
            $payload[$key] = $value;
        }

        session([config('form-builder.drafts.session_key', 'form_builder_drafts').'.'.$form->uuid => $payload]);
    }

    protected function clearDraft(Form $form): void
    {
        session()->forget(config('form-builder.drafts.session_key', 'form_builder_drafts').'.'.$form->uuid);
    }
}
