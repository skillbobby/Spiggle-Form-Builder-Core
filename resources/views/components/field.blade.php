@php
    $name = $field['name'] ?? 'field';
    $type = $field['type'] ?? 'text';
    $span = max(1, min(12, (int) ($field['column_span'] ?? 12)));
    $label = $field['label_override'] ?: ($field['label'] ?? $name);
    $position = $field['label_position'] ?: $formModel->labelPosition();
    $required = ! empty($field['required']);
    $placeholder = $position === 'inside' ? ($field['placeholder'] ?: $label) : ($field['placeholder'] ?? '');
    $hint = $field['hint'] ?? null;
    $options = $field['options'] ?? [];
    $errorKey = 'data.'.$name;
    $id = 'fb-'.$formModel->id.'-'.$name;
    $described = $id.'-hint';
    $isVisible = \Spiggle\FormBuilder\Support\FieldVisibility::isVisible($field, $this->data ?? []);
@endphp

@if ($isVisible)

@php $isToggle = in_array($type, ['boolean', 'toggle'], true); @endphp
@php
    $showLabelAbove = ! $isToggle && ! in_array($position, ['inside', 'below'], true);
    $showLabelBelow = ! $isToggle && in_array($position, ['inside', 'below'], true);
    $requiredMark = $required ? ' <span class="fb-req" aria-hidden="true">*</span>' : '';
@endphp
<div class="fb-span-{{ $span }} fb-field {{ $position }} {{ $isToggle ? 'fb-field-toggle' : '' }} {{ $errors->has($errorKey) ? 'is-invalid' : '' }}" wire:key="field-{{ $name }}">
    <label @class(['fb-label', 'fb-hidden' => ! $showLabelAbove]) for="{{ $id }}">
        {{ $label }}{!! $requiredMark !!}
    </label>

    @if (in_array($type, ['select'], true))
        <select id="{{ $id }}" class="fb-select" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
            <option value="">{{ $placeholder ?: 'Choose…' }}</option>
            @foreach ($options as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    @elseif ($type === 'multi_select')
        <select id="{{ $id }}" class="fb-select" wire:model="data.{{ $name }}" multiple @required($required) aria-describedby="{{ $described }}">
            @foreach ($options as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    @elseif ($type === 'radio')
        <div class="fb-radio" role="radiogroup" aria-labelledby="{{ $id }}">
            @foreach ($options as $option)
                <label>
                    <input type="radio" wire:model="data.{{ $name }}" value="{{ $option['value'] }}" @required($required)>
                    {{ $option['label'] }}
                </label>
            @endforeach
        </div>
    @elseif ($type === 'tags')
        <div class="fb-tags" id="{{ $id }}-wrap">
            @foreach ($this->data[$name] ?? [] as $i => $tag)
                <span class="fb-tag">
                    {{ $tag }}
                    <button type="button" class="fb-tag-x" wire:click="removeTag('{{ $name }}', {{ $i }})" aria-label="Remove {{ $tag }}">&times;</button>
                </span>
            @endforeach
            <input id="{{ $id }}" class="fb-tag-input" type="text"
                placeholder="{{ $placeholder ?: 'Type a tag, then comma or Enter' }}"
                wire:model="tagDraft.{{ $name }}"
                wire:keydown.enter.prevent="commitTag('{{ $name }}')"
                wire:keydown.comma.prevent="commitTag('{{ $name }}')"
                aria-describedby="{{ $described }}">
        </div>
        <p class="fb-hint">Press comma or Enter to add a badge.</p>
    @elseif ($type === 'textarea' && ! empty($field['meta']['use_editor']))
        <div class="fi fb-rich" id="{{ $id }}">
            {{ $this->editorField($name) }}
        </div>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" class="fb-textarea" rows="{{ data_get($field, 'meta.rows', 4) }}"
            placeholder="{{ $placeholder }}" wire:model.blur="data.{{ $name }}" @required($required)
            aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}"
            aria-describedby="{{ $described }}"></textarea>
    @elseif ($isToggle)
        <label class="fb-toggle" for="{{ $id }}">
            <input type="checkbox" id="{{ $id }}" class="fb-toggle-input" wire:model.live="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
            <span class="fb-toggle-track" aria-hidden="true"><span class="fb-toggle-thumb"></span></span>
            <span class="fb-toggle-label">
                {{ $label }}{!! $requiredMark !!}
            </span>
        </label>
    @elseif ($type === 'file')
        <input id="{{ $id }}" class="fb-file" type="file" wire:model="data.{{ $name }}"
            {{ data_get($field, 'meta.multiple') ? 'multiple' : '' }}
            aria-describedby="{{ $described }}">
        <div wire:loading wire:target="data.{{ $name }}" class="fb-hint">Uploading…</div>
    @elseif (in_array($type, ['text', 'phone', 'number', 'date', 'datetime', 'email', 'url'], true))
        @include('form-builder::components.masked-input', [
            'field' => $field,
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'placeholder' => $placeholder,
            'required' => $required,
            'errorKey' => $errorKey,
            'described' => $described,
        ])

    @endif

    <label @class(['fb-label', 'fb-hidden' => ! $showLabelBelow]) for="{{ $id }}">
        {{ $label }}{!! $required ? ' <span class="fb-req">*</span>' : '' !!}
    </label>

    <p @class(['fb-hint', 'fb-hidden' => blank($hint)]) id="{{ $described }}">{{ $hint }}</p>

    @error($errorKey)
        <p class="fb-error" role="alert">{{ $message }}</p>
    @enderror
</div>
@endif
