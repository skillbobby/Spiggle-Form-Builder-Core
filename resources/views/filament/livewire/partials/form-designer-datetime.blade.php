@props([
    'wireModel',
    'change' => 'saveVisibility',
    'ariaLabel',
    'placeholder' => 'Select date…',
])

<label class="fd-schedule-field">
    <span class="fd-schedule-label">{{ $ariaLabel }}</span>
    <div class="fd-datetime-wrap" x-data>
        <span class="fd-datetime-prefix" aria-hidden="true">
            <x-filament::icon icon="heroicon-o-calendar" class="fd-datetime-icon" />
        </span>
        <input
            type="datetime-local"
            class="fd-datetime-input"
            wire:model="{{ $wireModel }}"
            wire:change="{{ $change }}"
            aria-label="{{ $ariaLabel }}"
            placeholder="{{ $placeholder }}"
            x-ref="picker"
            x-on:click="$refs.picker.showPicker?.()"
        >
        <button
            type="button"
            class="fd-datetime-suffix"
            aria-label="Open calendar for {{ $ariaLabel }}"
            tabindex="-1"
            x-on:click="$refs.picker.showPicker?.()"
        >
            <x-filament::icon icon="heroicon-o-clock" class="fd-datetime-icon" />
        </button>
    </div>
</label>
