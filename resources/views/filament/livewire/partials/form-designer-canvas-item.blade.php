@php

    $id = $item['id'] ?? '';

    $kind = $item['kind'] ?? 'field';

    $type = (string) ($item['type'] ?? '');

    $span = max(1, min(12, (int) ($item['column_span'] ?? 12)));

    $selected = $selectedId === $id && $selectedZone === $zone;

    $isSection = $kind === 'content' && $type === 'section';

    $chromeBleed = (bool) ($chromeBleed ?? false);

    $chromeOutside = (bool) ($chromeOutside ?? false);

    $placeholders = [

        'text' => 'Enter text…',

        'email' => 'name@example.com',

        'phone' => '(555) 000-0000',

        'number' => '0',

        'url' => 'https://',

        'date' => 'YYYY-MM-DD',

        'textarea' => 'Enter details…',

    ];

@endphp



@if ($isSection)

    @php

        $sectionZone = 'section_'.$id;

        $meta = $item['meta'] ?? [];

        $styles = \Spiggle\FormBuilder\Support\ContentBlockCatalog::sectionStyles($meta);

        unset($styles['border']);

        $styleStr = collect($styles)->map(fn ($value, $key) => $key.': '.$value)->implode('; ');

        $children = $item['children'] ?? [];

        $sectionTarget = ($insertTargetSectionId ?? null) === $id;

    @endphp



    <div

        class="fd-canvas-item fd-canvas-section fd-span-{{ $span }} {{ $selected ? 'selected' : '' }}"

        data-item-id="{{ $id }}"

        @click.stop="selectCanvasItem('{{ $id }}', 'form')"

        wire:key="canvas-item-{{ $id }}"

    >

        <div class="fd-item-head">

            <span class="fd-drag-handle" @click.stop role="button" tabindex="0" aria-label="Drag to reorder">⋮⋮</span>

            <span class="fd-field-card">

                <strong>{{ $contentLabels['section'] ?? 'Section container' }}</strong>

                <span>{{ $meta['title'] ?? 'Section' }}</span>

            </span>

        </div>



        <div

            class="fd-section-container {{ $sectionTarget ? 'is-target' : '' }}"

            style="{{ $styleStr }}"

            @click.stop="focusZone('{{ $sectionZone }}')"

        >

            @if (! empty($meta['show_title']))

                <div class="fd-section-header">

                    <strong>{{ $meta['title'] ?? 'Section' }}</strong>

                </div>

            @endif



            @if (! empty($meta['show_divider']) && ! empty($meta['show_title']))

                <hr class="fd-section-divider">

            @endif



            <div

                class="fd-grid fd-section-grid {{ $children === [] ? 'is-empty' : '' }}"

                data-fd-zone="{{ $sectionZone }}"

                data-fd-sort-group="fd-form"

                data-empty-label="Drop fields or content blocks here"

                wire:ignore.self

                wire:key="fd-zone-{{ $sectionZone }}"

                x-init="mountZone($el)"

                @click.stop="focusZone('{{ $sectionZone }}')"

            >

                @foreach ($children as $child)

                    @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [

                        'item' => $child,

                        'zone' => $sectionZone,

                    ])

                @endforeach

            </div>

        </div>

    </div>

@else

    <div

        class="fd-canvas-item fd-span-{{ $span }} {{ $selected ? 'selected' : '' }} {{ $chromeBleed ? 'fd-chrome-block-bleed' : '' }} {{ $chromeOutside ? 'fd-chrome-block-outside' : '' }}"

        data-item-id="{{ $id }}"

        @click.stop="selectCanvasItem('{{ $id }}', '{{ $zone }}')"

        wire:key="canvas-item-{{ $id }}"

    >

        <div class="fd-item-head">

            <span class="fd-drag-handle" @click.stop role="button" tabindex="0" aria-label="Drag to reorder">⋮⋮</span>

            @if ($kind === 'content')

                <span class="fd-field-card">

                    <strong>{{ $contentLabels[$type] ?? 'Content' }}</strong>

                    <span>{{ $type }}</span>

                </span>

            @else

                <span class="fd-field-card">

                    <strong>{{ $item['label'] ?? $item['name'] }}</strong>

                    <span>{{ $item['type'] ?? 'text' }} · {{ $span }}/12 width</span>

                </span>

            @endif

        </div>



        @if ($kind === 'content')

            @include('form-builder::components.content-block', [
                'block' => $item,
                'preview' => true,
                'bleed' => $chromeBleed,
            ])

            @if ($chromeOutside)
                <p class="fd-chrome-outside-hint">Renders outside the form card on the live form.</p>
            @endif

        @else

            <div class="fd-field-preview">
                @if (in_array($item['type'] ?? '', ['boolean', 'toggle'], true))
                    <div class="fd-field-preview-toggle">
                        @include('form-builder::filament.livewire.partials.form-designer-switch', [
                            'label' => $item['label'] ?? $item['name'],
                            'checked' => false,
                            'action' => null,
                            'inline' => false,
                        ])
                    </div>
                @else
                <span class="fd-field-preview-label">

                    {{ $item['label'] ?? $item['name'] }}

                    @if (! empty($item['required']))

                        <span class="fd-field-preview-req">*</span>

                    @endif

                </span>

                <div class="fd-field-preview-control" aria-hidden="true">

                    @php
                        $maskPreview = \Spiggle\FormBuilder\Support\InputMaskCatalog::normalizeMask($item['meta']['input_mask'] ?? null);
                        $previewPlaceholder = $item['placeholder']
                            ?? ($maskPreview
                                ? \Spiggle\FormBuilder\Support\InputMaskCatalog::placeholder($maskPreview)
                                : ($placeholders[$item['type'] ?? 'text'] ?? 'Enter a value…'));
                    @endphp
                    {{ $previewPlaceholder }}

                </div>
                @endif
            </div>

        @endif

    </div>

@endif

