@if ($selectedItem = $this->selectedItem)
    @php
        $item = $selectedItem;
        $isContent = ($item['kind'] ?? 'field') === 'content';
        $type = (string) ($item['type'] ?? 'text');
        $usesRichEditor = $isContent && in_array($type, ['paragraph', 'html', 'heading', 'footer'], true);
        $usesImageUpload = $isContent && in_array($type, ['banner', 'image'], true);
    @endphp

    @if ($usesRichEditor)
        <label class="fd-field fd-rich-editor">
            <span>{{ $type === 'html' ? 'HTML content' : 'Rich text' }}</span>
            <div wire:key="inspector-rich-{{ $selectedId }}-{{ $selectedZone }}">
                {{ $this->inspectorRichEditorField() }}
            </div>
        </label>
    @endif

    @if ($isContent)
        @if ($type === 'heading')
            <label class="fd-field">
                <span>Level</span>
                <input type="number" min="1" max="4" class="fd-input"
                    value="{{ $this->selectedMeta('level', 2) }}"
                    wire:change="updateSelected('meta.level', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Alignment</span>
                <select class="fd-input" wire:change="updateSelected('meta.alignment', $event.target.value)">
                    @foreach (['left', 'center', 'right'] as $align)
                        <option value="{{ $align }}" @selected($this->selectedMeta('alignment', 'left') === $align)>{{ ucfirst($align) }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if ($type === 'paragraph')
            <label class="fd-field">
                <span>Alignment</span>
                <select class="fd-input" wire:change="updateSelected('meta.alignment', $event.target.value)">
                    @foreach (['left', 'center', 'right'] as $align)
                        <option value="{{ $align }}" @selected($this->selectedMeta('alignment', 'left') === $align)>{{ ucfirst($align) }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if ($type === 'spacer')
            <label class="fd-field">
                <span>Height</span>
                <input type="text" class="fd-input" placeholder="24px"
                    value="{{ $this->selectedMeta('height', '24px') }}"
                    wire:change="updateSelected('meta.height', $event.target.value)">
            </label>
        @endif

        @if ($type === 'section')
            <label class="fd-field">
                <span>Section title</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('title', 'Section') }}"
                    wire:change="updateSelected('meta.title', $event.target.value)">
            </label>
            @include('form-builder::filament.livewire.partials.form-designer-switch', [
                'label' => 'Show title',
                'checked' => (bool) $this->selectedMeta('show_title', true),
                'action' => "toggleSelectedMetaBool('show_title', true)",
                'inspector' => true,
            ])
            @include('form-builder::filament.livewire.partials.form-designer-switch', [
                'label' => 'Show divider',
                'checked' => (bool) $this->selectedMeta('show_divider', true),
                'action' => "toggleSelectedMetaBool('show_divider', true)",
                'inspector' => true,
            ])
            <label class="fd-field">
                <span>Border width</span>
                <input type="text" class="fd-input" placeholder="1px"
                    value="{{ $this->selectedMeta('border_width', '1px') }}"
                    wire:change="updateSelected('meta.border_width', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Border color</span>
                <input type="text" class="fd-input" placeholder="#e5e7eb"
                    value="{{ $this->selectedMeta('border_color', '#e5e7eb') }}"
                    wire:change="updateSelected('meta.border_color', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Corner radius</span>
                <input type="text" class="fd-input" placeholder="12px"
                    value="{{ $this->selectedMeta('border_radius', '12px') }}"
                    wire:change="updateSelected('meta.border_radius', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Background</span>
                <input type="text" class="fd-input" placeholder="#ffffff"
                    value="{{ $this->selectedMeta('background', '#ffffff') }}"
                    wire:change="updateSelected('meta.background', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Padding</span>
                <input type="text" class="fd-input" placeholder="1.25rem"
                    value="{{ $this->selectedMeta('padding', '1.25rem') }}"
                    wire:change="updateSelected('meta.padding', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Shadow</span>
                <select class="fd-input" wire:change="updateSelected('meta.shadow', $event.target.value)">
                    @foreach (['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'] as $value => $label)
                        <option value="{{ $value }}" @selected($this->selectedMeta('shadow', 'sm') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if ($type === 'banner')
            <label class="fd-field">
                <span>Height</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('height', '160px') }}"
                    wire:change="updateSelected('meta.height', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Caption</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('caption') }}"
                    wire:change="updateSelected('meta.caption', $event.target.value)">
            </label>
        @endif

        @if ($type === 'image')
            <label class="fd-field">
                <span>Alt text</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('alt') }}"
                    wire:change="updateSelected('meta.alt', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Caption</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('caption') }}"
                    wire:change="updateSelected('meta.caption', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>Max height</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('max_height', '320px') }}"
                    wire:change="updateSelected('meta.max_height', $event.target.value)">
            </label>
        @endif

        @if ($usesImageUpload)
            <div class="fd-field">
                <span>Image</span>
                <label class="fd-image-upload">
                    @if ($url = \Spiggle\FormBuilder\Support\StorageUrl::resolve($this->selectedMeta('image_url')))
                        <img src="{{ $url }}" alt="" class="fd-image-preview">
                        <div class="fd-image-actions">
                            <span class="fd-btn fd-btn-ghost">Replace</span>
                            <button type="button" class="fd-btn fd-btn-danger" wire:click.stop="removeSelectedImage">Remove</button>
                        </div>
                    @else
                        <p class="fd-hint">Click to upload an image</p>
                        <span class="fd-btn" style="margin-top:.5rem;display:inline-block">Upload image</span>
                    @endif
                    <input type="file" accept="image/*" wire:model="imageUpload" hidden>
                    <div wire:loading wire:target="imageUpload" class="fd-hint">Uploading…</div>
                </label>
            </div>
        @endif

        @if ($type === 'video')
            <label class="fd-field">
                <span>Video URL</span>
                <input type="url" class="fd-input" placeholder="https://youtube.com/..."
                    value="{{ $this->selectedMeta('url') }}"
                    wire:change="updateSelected('meta.url', $event.target.value)">
            </label>
        @endif

        @if ($type === 'button')
            <label class="fd-field">
                <span>Button text</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('text') }}"
                    wire:change="updateSelected('meta.text', $event.target.value)">
            </label>
            <label class="fd-field">
                <span>URL</span>
                <input type="url" class="fd-input"
                    value="{{ $this->selectedMeta('url') }}"
                    wire:change="updateSelected('meta.url', $event.target.value)">
            </label>
        @endif

        @if ($type === 'footer')
            <label class="fd-field">
                <span>Alignment</span>
                <select class="fd-input" wire:change="updateSelected('meta.alignment', $event.target.value)">
                    @foreach (['left', 'center', 'right'] as $align)
                        <option value="{{ $align }}" @selected($this->selectedMeta('alignment', 'center') === $align)>{{ ucfirst($align) }}</option>
                    @endforeach
                </select>
            </label>
            @include('form-builder::filament.livewire.partials.form-designer-switch', [
                'label' => 'Muted style',
                'checked' => (bool) $this->selectedMeta('muted', true),
                'action' => "toggleSelectedMetaBool('muted', true)",
                'inspector' => true,
            ])
        @endif

        @if ($type === 'social_links')
            <div class="fd-field">
                <span>Social links</span>
                <div class="fd-repeater-list">
                    @foreach ($this->selectedMeta('links', []) as $li => $link)
                        <div class="fd-repeater-row" wire:key="social-{{ $selectedId }}-{{ $li }}">
                            <div class="fd-repeater-row-head">
                                <span>Link {{ $li + 1 }}</span>
                                <button type="button" class="fd-icon-btn" wire:click="removeSelectedSocialLink({{ $li }})" aria-label="Remove link">&times;</button>
                            </div>
                            <select class="fd-input" wire:change="updateSelectedSocialLink({{ $li }}, 'platform', $event.target.value)">
                                @foreach (['linkedin', 'twitter', 'facebook', 'instagram', 'youtube', 'github', 'website', 'other'] as $platform)
                                    <option value="{{ $platform }}" @selected(($link['platform'] ?? '') === $platform)>{{ ucfirst($platform) }}</option>
                                @endforeach
                            </select>
                            <input type="url" class="fd-input" placeholder="https://"
                                value="{{ $link['url'] ?? '' }}"
                                wire:change="updateSelectedSocialLink({{ $li }}, 'url', $event.target.value)">
                        </div>
                    @endforeach
                </div>
                <button type="button" class="fd-btn fd-btn-ghost" wire:click="addSelectedSocialLink" style="margin-top:.35rem">+ Add link</button>
            </div>
        @endif

        @if ($type === 'button_group')
            <div class="fd-field">
                <span>Buttons</span>
                <div class="fd-repeater-list">
                    @foreach ($this->selectedMeta('buttons', []) as $bi => $btn)
                        <div class="fd-repeater-row" wire:key="btn-group-{{ $selectedId }}-{{ $bi }}">
                            <div class="fd-repeater-row-head">
                                <span>Button {{ $bi + 1 }}</span>
                                <button type="button" class="fd-icon-btn" wire:click="removeSelectedButtonGroupItem({{ $bi }})" aria-label="Remove button">&times;</button>
                            </div>
                            <input type="text" class="fd-input" placeholder="Title"
                                value="{{ $btn['title'] ?? '' }}"
                                wire:change="updateSelectedButtonGroupItem({{ $bi }}, 'title', $event.target.value)">
                            <input type="text" class="fd-input" placeholder="Label"
                                value="{{ $btn['text'] ?? '' }}"
                                wire:change="updateSelectedButtonGroupItem({{ $bi }}, 'text', $event.target.value)">
                            <input type="url" class="fd-input" placeholder="https://"
                                value="{{ $btn['url'] ?? '' }}"
                                wire:change="updateSelectedButtonGroupItem({{ $bi }}, 'url', $event.target.value)">
                        </div>
                    @endforeach
                </div>
                <button type="button" class="fd-btn fd-btn-ghost" wire:click="addSelectedButtonGroupItem" style="margin-top:.35rem">+ Add button</button>
            </div>
        @endif
    @else
        <label class="fd-field">
            <span>Label</span>
            <input type="text" class="fd-input" value="{{ $item['label'] ?? '' }}"
                wire:change="updateSelected('label', $event.target.value)">
        </label>
        <label class="fd-field">
            <span>Internal name</span>
            <input type="text" class="fd-input" value="{{ $item['name'] ?? '' }}"
                wire:change="updateSelected('name', $event.target.value)">
        </label>
        <label class="fd-field">
            <span>Type</span>
            <input type="text" class="fd-input" value="{{ $item['type'] ?? '' }}" disabled>
        </label>
        <label class="fd-field">
            <span>Label position</span>
            <select class="fd-input" wire:change="updateSelected('label_position', $event.target.value || null)">
                <option value="">Default</option>
                @foreach ($labelPositionLabels as $value => $label)
                    <option value="{{ $value }}" @selected(($item['label_position'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        @include('form-builder::filament.livewire.partials.form-designer-switch', [
            'label' => 'Required',
            'checked' => ! empty($item['required']),
            'action' => "toggleSelectedBoolean('required')",
            'inspector' => true,
        ])
        <label class="fd-field">
            <span>Placeholder</span>
            <input type="text" class="fd-input" value="{{ $item['placeholder'] ?? '' }}"
                wire:change="updateSelected('placeholder', $event.target.value)">
        </label>
        <label class="fd-field">
            <span>Hint</span>
            <input type="text" class="fd-input" value="{{ $item['hint'] ?? '' }}"
                wire:change="updateSelected('hint', $event.target.value)">
        </label>

        @if (\Spiggle\FormBuilder\Support\InputMaskCatalog::isMaskableFieldType($type))
            <label class="fd-field">
                <span>Input mask{{ ! $proUnlocked ? ' · PRO' : '' }}</span>
                <select class="fd-input" wire:change="updateSelectedInputMask($event.target.value || null)">
                    @foreach (\Spiggle\FormBuilder\Support\InputMaskCatalog::labels() as $value => $label)
                        <option value="{{ $value }}" @selected(($item['meta']['input_mask'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @if (! $proUnlocked)
                <p class="fd-hint">Upgrade to Pro to format phone numbers, dates, times, and currency as visitors type.</p>
            @elseif (! empty($item['meta']['input_mask']))
                <p class="fd-hint">Uses placeholder {{ \Spiggle\FormBuilder\Support\InputMaskCatalog::placeholder($item['meta']['input_mask']) }} when the field placeholder is blank. Mobile keyboards switch automatically.</p>
            @endif
        @endif

        @if ($type === 'textarea')
            @include('form-builder::filament.livewire.partials.form-designer-switch', [
                'label' => 'Use rich editor',
                'checked' => ! empty($item['meta']['use_editor']),
                'action' => "toggleSelectedMetaBool('use_editor')",
                'inspector' => true,
            ])
        @endif

        @if (\Spiggle\FormBuilder\Support\FieldCatalog::requiresOptions($type))
            <div class="fd-field">
                <span>Options</span>
                <div class="fd-options-list">
                    @foreach ($item['options'] ?? [] as $oi => $option)
                        <div class="fd-option-row" wire:key="opt-{{ $selectedId }}-{{ $oi }}">
                            <input type="text" class="fd-input" placeholder="Label" value="{{ $option['label'] ?? '' }}"
                                wire:change="updateSelectedOption({{ $oi }}, 'label', $event.target.value)">
                            <input type="text" class="fd-input" placeholder="Value" value="{{ $option['value'] ?? '' }}"
                                wire:change="updateSelectedOption({{ $oi }}, 'value', $event.target.value)">
                            <button type="button" class="fd-icon-btn" wire:click="removeSelectedOption({{ $oi }})" aria-label="Remove option">&times;</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="fd-btn fd-btn-ghost" wire:click="addSelectedOption" style="margin-top:.35rem">+ Add option</button>
            </div>
        @endif

        <label class="fd-field">
            <span>Validation rules</span>
            <input type="text" class="fd-input" placeholder="min:3, max:120"
                value="{{ implode(', ', $item['validation_rules'] ?? []) }}"
                wire:change="updateSelected('validation_rules', $event.target.value.split(',').map(s => s.trim()).filter(Boolean))">
        </label>

        <label class="fd-field">
            <span>Visible when field</span>
            <select class="fd-input" wire:change="updateSelected('meta.visible_when_field', $event.target.value || null)">
                <option value="">Always visible</option>
                @foreach ($this->allFieldNames() as $fieldName)
                    @if ($fieldName !== ($item['name'] ?? ''))
                        <option value="{{ $fieldName }}" @selected($this->selectedMeta('visible_when_field') === $fieldName)>{{ $fieldName }}</option>
                    @endif
                @endforeach
            </select>
        </label>
        @if ($this->selectedMeta('visible_when_field'))
            <label class="fd-field">
                <span>Equals value</span>
                <input type="text" class="fd-input"
                    value="{{ $this->selectedMeta('visible_when_value') }}"
                    wire:change="updateSelected('meta.visible_when_value', $event.target.value)">
            </label>
        @endif
    @endif

    <label class="fd-field">
        <span>Width (1–12 columns)</span>
        <input type="number" min="1" max="12" class="fd-input"
            value="{{ $item['column_span'] ?? 12 }}"
            wire:change="updateSelected('column_span', $event.target.value)">
    </label>
@endif
