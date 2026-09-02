@props([
    'label',
    'checked' => false,
    'action' => null,
    'inspector' => false,
    'inline' => false,
    'hint' => null,
    'variant' => null,
])

<div @class([
    'fd-switch-field',
    'fd-switch-field-inline' => $inline,
    'fd-switch-field-stacked' => ! $inline && $variant,
    'fd-inspector-field' => $inspector,
])>
    <span class="fd-switch-label">{{ $label }}</span>
    <button
        type="button"
        role="switch"
        aria-label="{{ $label }}"
        x-data="{ on: @js($checked) }"
        @click="on = ! on"
        @class([
            'fd-switch',
            'is-published' => $variant === 'published',
            'is-active' => $variant === 'active',
        ])
        :class="{ 'is-on': on }"
        :aria-checked="on ? 'true' : 'false'"
        @if ($action)
            wire:click.stop="{{ $action }}"
        @endif
        @if ($hint)
            title="{{ $hint }}"
        @endif
    >
        <span class="fd-switch-track" aria-hidden="true">
            <span class="fd-switch-thumb"></span>
        </span>
    </button>
</div>
