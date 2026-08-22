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
@endphp

<div class="fb-span-{{ $span }} fb-field {{ $position }} {{ $errors->has($errorKey) ? 'is-invalid' : '' }}" wire:key="field-{{ $name }}">
    @if ($position !== 'inside' && $position !== 'below')
        <label class="fb-label" for="{{ $id }}">
            {{ $label }}@if ($required) <span class="fb-req" aria-hidden="true">*</span>@endif
        </label>
    @endif

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
    @elseif (in_array($type, ['boolean', 'toggle'], true))
        <label>
            <input type="checkbox" wire:model="data.{{ $name }}">
            {{ $label }}
        </label>
    @elseif ($type === 'file')
        <input id="{{ $id }}" class="fb-file" type="file" wire:model="data.{{ $name }}"
            @if (data_get($field, 'meta.multiple')) multiple @endif
            aria-describedby="{{ $described }}">
        <div wire:loading wire:target="data.{{ $name }}" class="fb-hint">Uploading…</div>
    @elseif ($type === 'date')
        <input id="{{ $id }}" class="fb-input" type="date" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
    @elseif ($type === 'datetime')
        <input id="{{ $id }}" class="fb-input" type="datetime-local" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
    @elseif ($type === 'number')
        <input id="{{ $id }}" class="fb-input" type="number" placeholder="{{ $placeholder }}" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
    @elseif ($type === 'email')
        <input id="{{ $id }}" class="fb-input" type="email" placeholder="{{ $placeholder }}" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
    @elseif ($type === 'phone')
        <input id="{{ $id }}" class="fb-input" type="tel" placeholder="{{ $placeholder }}" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
    @elseif ($type === 'url')
        <input id="{{ $id }}" class="fb-input" type="url" placeholder="{{ $placeholder }}" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
    @else
        <input id="{{ $id }}" class="fb-input" type="text" placeholder="{{ $placeholder }}" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
    @endif

    @if ($position === 'below' || $position === 'inside')
        <label class="fb-label" for="{{ $id }}">
            {{ $label }}@if ($required) <span class="fb-req">*</span>@endif
        </label>
    @endif

    @if ($hint)
        <p class="fb-hint" id="{{ $described }}">{{ $hint }}</p>
    @else
        <span id="{{ $described }}" class="fb-hidden"></span>
    @endif

    @error($errorKey)
        <p class="fb-error" role="alert">{{ $message }}</p>
    @enderror
</div>
