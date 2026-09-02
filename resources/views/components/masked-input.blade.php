@php
    $inputMask = \Spiggle\FormBuilder\Support\InputMaskCatalog::resolveForField($field);
    $displayPlaceholder = $inputMask
        ? ($placeholder ?: \Spiggle\FormBuilder\Support\InputMaskCatalog::placeholder($inputMask))
        : $placeholder;
    $htmlType = $inputMask
        ? \Spiggle\FormBuilder\Support\InputMaskCatalog::inputType($inputMask, $type)
        : $type;
@endphp

@if ($type === 'date')
    <input id="{{ $id }}" class="fb-input" type="date" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
@elseif ($type === 'datetime')
    <input id="{{ $id }}" class="fb-input" type="datetime-local" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
@elseif ($inputMask)
    <input
        id="{{ $id }}"
        class="fb-input"
        type="{{ $htmlType }}"
        placeholder="{{ $displayPlaceholder }}"
        inputmode="{{ \Spiggle\FormBuilder\Support\InputMaskCatalog::inputMode($inputMask) }}"
        autocomplete="off"
        wire:model.blur="data.{{ $name }}"
        @required($required)
        aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}"
        aria-describedby="{{ $described }}"
        x-data="fbInputMask(@js($inputMask))"
        x-on:input="handleInput($event)"
    >
@elseif ($type === 'number')
    <input id="{{ $id }}" class="fb-input" type="number" placeholder="{{ $displayPlaceholder }}" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
@elseif ($type === 'email')
    <input id="{{ $id }}" class="fb-input" type="email" placeholder="{{ $displayPlaceholder }}" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
@elseif ($type === 'phone')
    <input id="{{ $id }}" class="fb-input" type="tel" placeholder="{{ $displayPlaceholder }}" wire:model="data.{{ $name }}" @required($required) aria-describedby="{{ $described }}">
@elseif ($type === 'url')
    <input id="{{ $id }}" class="fb-input" type="url" placeholder="{{ $displayPlaceholder }}" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
@else
    <input id="{{ $id }}" class="fb-input" type="text" placeholder="{{ $displayPlaceholder }}" wire:model.blur="data.{{ $name }}" @required($required) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $described }}">
@endif
