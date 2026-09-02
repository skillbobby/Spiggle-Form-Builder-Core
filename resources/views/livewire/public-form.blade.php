@php
    $containers = $formModel->schema ?? [];
    $type = $this->resolvedLayout();
    $total = max(1, count($containers));
    $progress = (int) round((($step + 1) / $total) * 100);
    $navigable = in_array($type, ['wizard', 'pages', 'tabs', 'accordion'], true);
    $thankYou = $this->thankYouSettings();
    $thankYouComponent = \Spiggle\FormBuilder\Support\ThankYouLayouts::component((string) ($thankYou['layout'] ?? 'card'));
@endphp

<div>
    @if ($submitted)
        @include($thankYouComponent, [
            'formName' => $formModel->name,
            'thankYou' => $thankYou,
            'submittedAt' => $submittedAt,
            'redirectUrl' => $redirectUrl,
        ])
    @else
        @if ($unavailabilityReason)
            <div class="fb-card">
                <h1 class="fb-title">{{ $formModel->name }}</h1>
                <p @class(['fb-desc', 'fb-hidden' => blank($formModel->description)])>{{ $formModel->description }}</p>
                <div class="fb-alert" role="status">
                    <strong>{{ $unavailabilityReason }}</strong>
                    <p class="fb-hint" style="margin:.35rem 0 0">Please check back later or contact the form owner if you believe this is an error.</p>
                </div>
            </div>
        @else
        <div @class(['fb-alert', 'fb-hidden' => ! $previewMode]) role="status" style="margin-bottom:1rem;border-color:#fde68a;background:#fffbeb;color:#92400e">
            <strong>Draft preview</strong>
            <p class="fb-hint" style="margin:.35rem 0 0">This form is not published yet. Publish and save in the admin builder before sharing the public URL.</p>
        </div>

        @if ($this->pageChromeBlocks('header', 'outside') !== [])
            <div class="fb-chrome-outside-above">
                @include('form-builder::components.page-chrome-zone', [
                    'zone' => 'header',
                    'blocks' => $this->pageChromeBlocks('header', 'outside'),
                    'formModel' => $formModel,
                ])
            </div>
        @endif

        <form
            @class([
                'fb-card',
                'fb-form-card',
                'fb-has-chrome-bleed-header' => $this->pageChromeZoneBleeds('header') && $this->pageChromeBlocks('header', 'inside') !== [],
                'fb-has-chrome-bleed-footer' => $this->pageChromeZoneBleeds('footer') && $this->pageChromeBlocks('footer', 'inside') !== [],
            ])
            wire:submit="submit"
            novalidate
        >
            @include('form-builder::components.page-chrome-zone', [
                'zone' => 'header',
                'blocks' => $this->pageChromeBlocks('header', 'inside'),
                'bleed' => $this->pageChromeZoneBleeds('header'),
                'blockBleeds' => fn (array $block): bool => $this->pageChromeBlockBleeds($block, 'header'),
                'formModel' => $formModel,
            ])

            <h1 class="fb-title">{{ $formModel->name }}</h1>
            <p @class(['fb-desc', 'fb-hidden' => blank($formModel->description)])>{{ $formModel->description }}</p>

            <div @class(['fb-alert', 'fb-hidden' => ! $errors->any()]) role="alert">
                    <strong>Please fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('error', $errors->count()) }}.</strong>
                    <p class="fb-hint" style="margin:.35rem 0 0">We moved you to the first field that needs attention.</p>
                    <ul>
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
            </div>

            <div @class(['fb-progress', 'fb-hidden' => ! in_array($type, ['wizard', 'pages'], true)]) aria-hidden="true"><span style="width: {{ $progress }}%"></span></div>
            <p @class(['fb-hint', 'fb-hidden' => ! in_array($type, ['wizard', 'pages'], true)]) id="fb-step-status">Step {{ $step + 1 }} of {{ $total }}: {{ $containers[$step]['label'] ?? 'Details' }}</p>

            <div @class(['fb-pills', 'fb-hidden' => $type !== 'tabs']) role="tablist" aria-label="{{ $formModel->name }} sections">
                    @foreach ($containers as $i => $container)
                        <button type="button" class="fb-pill" role="tab"
                            wire:click="goToStep({{ $i }})"
                            aria-selected="{{ $step === $i ? 'true' : 'false' }}">
                            {{ $container['label'] ?? 'Tab '.($i + 1) }}
                        </button>
                    @endforeach
            </div>

            @foreach ($containers as $i => $container)
                @php
                    $isAccordion = $type === 'accordion';
                    $visible = $type === 'single'
                        || ($isAccordion && in_array($i, $openSections, true))
                        || (! $isAccordion && $i === $step);
                @endphp

                @if ($isAccordion)
                    <div @class(['fb-accordion-item', 'is-open' => $visible]) wire:key="fb-acc-{{ $i }}">
                        <h2 class="fb-acc-h">
                            <button type="button" class="fb-acc-btn" wire:click="toggleSection({{ $i }})"
                                aria-expanded="{{ $visible ? 'true' : 'false' }}">
                                <span>{{ $container['label'] ?? 'Section '.($i + 1) }}</span>
                                <span class="fb-acc-icon" aria-hidden="true"></span>
                            </button>
                        </h2>
                        <div class="fb-acc-panel" aria-hidden="{{ $visible ? 'false' : 'true' }}">
                            <div class="fb-acc-panel-inner">
                                <p @class(['fb-desc', 'fb-hidden' => empty($container['description'])])>{{ $container['description'] ?? '' }}</p>
                                <div class="fb-grid">
                                    @foreach ($container['fields'] ?? [] as $item)
                                        @if (\Spiggle\FormBuilder\Support\ContentBlockCatalog::isContent($item))
                                            @include('form-builder::components.content-block', [
                                                'block' => $item,
                                                'formModel' => $formModel,
                                            ])
                                        @else
                                            @include('form-builder::components.field', ['field' => $item, 'formModel' => $formModel])
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <section @class(['fb-tab-panel', 'is-active' => $visible, 'fb-hidden' => ! $visible])
                        wire:key="fb-step-{{ $i }}"
                        role="{{ $type === 'tabs' ? 'tabpanel' : 'region' }}"
                        aria-label="{{ $container['label'] ?? 'Section' }}">
                        <p @class(['fb-desc', 'fb-hidden' => $type === 'single' || empty($container['description'])])>{{ $container['description'] ?? '' }}</p>
                        <div class="fb-grid">
                            @foreach ($container['fields'] ?? [] as $item)
                                @if (\Spiggle\FormBuilder\Support\ContentBlockCatalog::isContent($item))
                                    @include('form-builder::components.content-block', [
                                        'block' => $item,
                                        'formModel' => $formModel,
                                    ])
                                @else
                                    @include('form-builder::components.field', ['field' => $item, 'formModel' => $formModel])
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <div class="fb-actions">
                @if ($navigable && $step > 0)
                    <button type="button" class="fb-btn secondary" wire:click="previousStep">Back</button>
                @endif

                @if ($navigable && $step < $total - 1)
                    <button type="button" class="fb-btn" wire:click="nextStep">
                        {{ $type === 'pages' ? 'Save and continue' : 'Next' }}
                    </button>
                @endif

                @if (! $navigable || $step === $total - 1)
                    <button type="submit" class="fb-btn" wire:loading.attr="disabled" @disabled($previewMode)>Submit</button>
                @endif
            </div>

            @include('form-builder::components.page-chrome-zone', [
                'zone' => 'footer',
                'blocks' => $this->pageChromeBlocks('footer', 'inside'),
                'bleed' => $this->pageChromeZoneBleeds('footer'),
                'blockBleeds' => fn (array $block): bool => $this->pageChromeBlockBleeds($block, 'footer'),
                'formModel' => $formModel,
            ])
        </form>

        @if ($this->pageChromeBlocks('footer', 'outside') !== [])
            <div class="fb-chrome-outside-below">
                @include('form-builder::components.page-chrome-zone', [
                    'zone' => 'footer',
                    'blocks' => $this->pageChromeBlocks('footer', 'outside'),
                    'formModel' => $formModel,
                ])
            </div>
        @endif
        <x-filament-actions::modals />
        @endif
    @endif
</div>
