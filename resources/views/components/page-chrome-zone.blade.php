@php
    $zone = $zone ?? 'header';
    $blocks = $blocks ?? [];
    $bleed = (bool) ($bleed ?? false);
    $formModel = $formModel ?? null;
    $preview = (bool) ($preview ?? false);
    $blockBleeds = $blockBleeds ?? null;
@endphp

@if ($blocks !== [])
    <div @class([
        'fb-chrome-zone',
        'fb-chrome-header' => $zone === 'header',
        'fb-chrome-footer' => $zone === 'footer',
        'fb-chrome-bleed' => $bleed,
    ])>
        @foreach ($blocks as $block)
            @include('form-builder::components.content-block', [
                'block' => $block,
                'formModel' => $formModel,
                'preview' => $preview,
                'bleed' => is_callable($blockBleeds) ? $blockBleeds($block) : (bool) $bleed,
            ])
        @endforeach
    </div>
@endif
