@php
    $zone = $zone ?? 'header';
    $label = $label ?? 'Form header';
    $wrapperClass = $wrapperClass ?? null;
    $bleed = (bool) ($bleed ?? false);
    $emptyLabel = $emptyLabel ?? '+ Add content';
    $blocks = $zone === 'header' ? $this->headerBlocks : $this->footerBlocks;
    $fdZoneInit = 'mountZone($el)';
@endphp

<div @class(array_filter([
    $wrapperClass,
    'fd-chrome-zone-wrap',
    'fd-chrome-zone-wrap-'.$zone,
]))>
    <p class="fd-zone-label">{{ $label }}</p>

    <div @class([
        'fd-chrome-zone',
        'fb-chrome-zone',
        'fb-chrome-header' => $zone === 'header',
        'fb-chrome-footer' => $zone === 'footer',
        'fb-chrome-bleed' => $bleed,
    ])>
        <div
            @class([
                'fd-grid',
                $blocks === [] ? 'fd-drop-zone is-empty' : '',
                $insertTarget === $zone ? 'is-target' : '',
            ])
            data-fd-zone="{{ $zone }}"
            data-fd-sort-group="fd-form"
            data-empty-label="{{ $emptyLabel }}"
            wire:ignore.self
            wire:key="fd-zone-{{ $zone }}"
            x-init="{{ $fdZoneInit }}"
            @click="focusZone('{{ $zone }}')"
            @if ($blocks === [])
                role="button"
                tabindex="0"
                aria-label="Add {{ $zone }} content"
            @endif
        >
            @foreach ($blocks as $block)
                @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [
                    'item' => $block,
                    'zone' => $zone,
                    'chromeBleed' => $this->pageChromeBlockBleeds($block, $zone),
                    'chromeOutside' => $this->pageChromeBlockOutside($block, $zone),
                ])
            @endforeach
        </div>

        @if ($blocks !== [])
            <button
                type="button"
                class="fd-drop-zone fd-drop-zone-sm {{ $insertTarget === $zone ? 'is-target' : '' }}"
                @click="focusZone('{{ $zone }}')"
            >
                <span class="fd-drop-zone-copy">+ Add to {{ $zone }}</span>
            </button>
        @endif
    </div>
</div>
