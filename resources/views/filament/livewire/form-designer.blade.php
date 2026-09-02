@php
  $fdZoneInit = 'mountZone($el)';
@endphp

<div    class="fd-root"
    wire:key="form-designer-{{ $formId }}"
    x-data="formDesignerCanvas(@js($panel), @js($paletteTab))"
    @keydown.window="handleKeydown($event)"
>
    <header class="fd-header">
        <div class="fd-top-bar">
            <div class="fd-top-bar-start">
                <label class="fd-view-label">
                    <span class="fd-view-label-text">View:</span>
                    <select class="fd-select fd-view-select" wire:model.live="containerType" aria-label="Layout">
                        @foreach ($layoutLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="fd-view-tabs" role="tablist" aria-label="Designer view">
                    <button type="button" class="fd-view-tab" :class="{ 'active': panel === 'form' }" @click="switchPanel('form')">
                        <svg class="fd-view-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                        Form canvas
                    </button>
                    <button type="button" class="fd-view-tab" :class="{ 'active': panel === 'thank_you' }" @click="switchPanel('thank_you')">
                        <svg class="fd-view-tab-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                        Thank you page
                    </button>
                </div>
            </div>
            <div class="fd-top-bar-actions">
                @if ($this->canOpenPublic())
                    <button type="button" class="fd-btn fd-btn-ghost" wire:click="openPublicForm" wire:loading.attr="disabled">
                        <svg class="fd-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <span wire:loading.remove wire:target="openPublicForm">Open public</span>
                        <span wire:loading wire:target="openPublicForm">Saving…</span>
                    </button>
                @else
                    <button type="button" class="fd-btn fd-btn-ghost" wire:click="previewDraft" wire:loading.attr="disabled">
                        <svg class="fd-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <span wire:loading.remove wire:target="previewDraft">Preview draft</span>
                        <span wire:loading wire:target="previewDraft">Saving…</span>
                    </button>
                @endif
                <button type="button" class="fd-btn fd-btn-primary" wire:click="save">
                    <svg class="fd-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Save changes
                </button>
            </div>
        </div>

        <div class="fd-settings-panel">
            <div class="fd-settings-col">
                <label class="fd-settings-field">
                    <span class="fd-settings-col-title">Form name</span>
                    <input
                        type="text"
                        class="fd-input fd-form-name-input"
                        wire:model.blur="name"
                        placeholder="Untitled form"
                        aria-label="Form name"
                    >
                </label>
                <p class="fd-settings-col-title fd-settings-col-title-spaced">Live share link</p>
                <div class="fd-share-row" x-data="{ copied: false }">
                    <input type="text" class="fd-input fd-share-input" readonly value="{{ $this->shareUrl() }}" aria-label="Share link">
                    <button
                        type="button"
                        class="fd-btn fd-btn-ghost fd-btn-copy"
                        x-on:click="navigator.clipboard.writeText(@js($this->shareUrl())); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <span x-show="!copied">Copy link</span>
                        <span x-show="copied" x-cloak>Copied!</span>
                    </button>
                </div>
                <p class="fd-settings-hint">{{ $this->shareUrlHint() }}</p>
            </div>

            <div class="fd-settings-col fd-settings-col-visibility">
                <p class="fd-settings-col-title">Visibility</p>
                <div class="fd-visibility-toggles">
                    @include('form-builder::filament.livewire.partials.form-designer-switch', [
                        'label' => 'Published',
                        'checked' => $isPublished,
                        'action' => 'togglePublished',
                        'variant' => 'published',
                        'hint' => 'When on, the form is reachable at its public URL.',
                    ])
                    @include('form-builder::filament.livewire.partials.form-designer-switch', [
                        'label' => 'Active',
                        'checked' => $isActive,
                        'action' => 'toggleActive',
                        'variant' => 'active',
                        'hint' => 'When off, the form stops accepting submissions.',
                    ])
                </div>
            </div>

            <div class="fd-settings-col fd-settings-col-schedule">
                <p class="fd-settings-col-title">Scheduling</p>
                <div class="fd-schedule-row">
                    @include('form-builder::filament.livewire.partials.form-designer-datetime', [
                        'wireModel' => 'activeFrom',
                        'ariaLabel' => 'Opens at',
                        'placeholder' => 'Select start date…',
                    ])
                    @include('form-builder::filament.livewire.partials.form-designer-datetime', [
                        'wireModel' => 'activeUntil',
                        'ariaLabel' => 'Closes at',
                        'placeholder' => 'Select end date…',
                    ])
                </div>
                <p class="fd-settings-hint fd-settings-hint-center">Open &amp; close dates</p>
            </div>
        </div>
    </header>

    <div class="fd-body">
        <aside class="fd-left">
            <div class="fd-palette-search">
                <input type="search" class="fd-input" placeholder="Search palette…" wire:model.live.debounce.300ms="paletteSearch" aria-label="Search palette">
            </div>

            <div class="fd-palette-tabs">
                <button type="button" class="fd-palette-tab" :class="{ 'active': paletteTab === 'fields' }" @click="switchPaletteTab('fields')">Fields</button>
                <button type="button" class="fd-palette-tab" :class="{ 'active': paletteTab === 'content' }" @click="switchPaletteTab('content')">Content</button>
            </div>

            @if ($insertTargetSectionId)
                <p class="fd-hint fd-insert-target-hint" x-show="panel === 'form'" x-cloak>
                    Adding to <strong>section container</strong>.
                    <button type="button" class="fd-btn fd-btn-ghost fd-btn-xs" wire:click="focusInsertTarget('form')">Reset</button>
                </p>
            @elseif ($insertTarget !== 'form')
                <p class="fd-hint fd-insert-target-hint" x-show="panel === 'form'" x-cloak>
                    Adding to <strong>{{ $insertTarget === 'header' ? 'header' : 'footer' }}</strong> zone.
                    <button type="button" class="fd-btn fd-btn-ghost fd-btn-xs" wire:click="focusInsertTarget('form')">Reset</button>
                </p>
            @endif

            <div x-show="paletteTab === 'fields' && panel === 'form'" x-cloak>
                @forelse ($fieldPaletteCategories as $category)
                    <div class="fd-palette-category">
                        <p class="fd-palette-category-title">{!! \Spiggle\FormBuilder\Support\PaletteCatalog::iconSvg($category['icon']) !!} {{ $category['label'] }}</p>
                        <div class="fd-palette-grid" data-fd-palette-grid>
                            @foreach ($category['items'] as $entry)
                                <div
                                    role="button"
                                    tabindex="0"
                                    class="fd-palette-tile"
                                    data-palette-kind="field"
                                    data-palette-type="{{ $entry['type'] }}"
                                    @click="onPaletteClick('field', '{{ $entry['type'] }}')"
                                    @keydown.enter.prevent="onPaletteClick('field', '{{ $entry['type'] }}')"
                                    title="{{ $entry['label'] }}"
                                >
                                    <span class="fd-palette-tile-icon">{!! \Spiggle\FormBuilder\Support\PaletteCatalog::typeIcon($entry['type']) !!}</span>
                                    <span class="fd-palette-tile-label">{{ $entry['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="fd-palette-empty">No fields match your search.</p>
                @endforelse
            </div>

            <p class="fd-palette-empty" x-show="paletteTab === 'fields' && panel === 'thank_you'" x-cloak>
                Switch to the Content tab to add thank-you blocks.
            </p>

            <div x-show="paletteTab === 'content'" x-cloak>
                @forelse ($contentPaletteCategories as $category)
                    <div class="fd-palette-category">
                        <p class="fd-palette-category-title">{!! \Spiggle\FormBuilder\Support\PaletteCatalog::iconSvg($category['icon']) !!} {{ $category['label'] }}</p>
                        <div class="fd-palette-grid" data-fd-palette-grid>
                            @foreach ($category['items'] as $entry)
                                <div
                                    role="button"
                                    tabindex="0"
                                    class="fd-palette-tile {{ $entry['pro'] ? 'is-pro' : '' }}"
                                    data-palette-kind="content"
                                    data-palette-type="{{ $entry['type'] }}"
                                    @click="onPaletteClick('content', '{{ $entry['type'] }}')"
                                    @keydown.enter.prevent="onPaletteClick('content', '{{ $entry['type'] }}')"
                                    title="{{ $entry['label'] }}"
                                >
                                    <span class="fd-palette-tile-icon">{!! \Spiggle\FormBuilder\Support\PaletteCatalog::typeIcon($entry['type']) !!}</span>
                                    <span class="fd-palette-tile-label">{{ $entry['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="fd-palette-empty">No content blocks match your search.</p>
                @endforelse
            </div>
        </aside>

        {{-- Inspector toggles only — never sortItem / addFromPalette / reorderZone (those would lock the canvas). --}}
        <div
            class="fd-canvas-wrap"
            wire:loading.class="fd-no-pointer"
            wire:target="toggleSelectedMetaBool, toggleSelectedBoolean, updateSelected"
        >
            <div data-fd-panel="thank_you" x-show="panel === 'thank_you'" x-cloak>
                @include('form-builder::filament.livewire.partials.form-designer-thank-you-settings')

                <main class="fd-canvas">
                    @include('form-builder::filament.livewire.partials.form-designer-thank-you-canvas')
                </main>
            </div>

            <div data-fd-panel="form" x-show="panel === 'form'" x-cloak>
                <div class="fd-canvas-toolbar">
                    @if ($containerType !== 'single')
                        <div class="fd-section-tabs">
                            @foreach ($schema as $i => $section)
                                <div class="fd-section-tab-wrap {{ $activeSection === $i ? 'is-active' : '' }}">
                                    <button type="button" class="fd-section-tab {{ $activeSection === $i ? 'active' : '' }}"
                                        wire:click="selectSection({{ $i }})">
                                        {{ $section['label'] ?? 'Section '.($i + 1) }}
                                    </button>
                                    @if (count($schema) > 1)
                                        <button
                                            type="button"
                                            class="fd-section-tab-remove"
                                            wire:click.stop="removeSection({{ $i }})"
                                            aria-label="Remove {{ $section['label'] ?? 'section '.($i + 1) }}"
                                            title="Remove page"
                                        >&times;</button>
                                    @endif
                                </div>
                            @endforeach
                            <button type="button" class="fd-section-add" wire:click="addSection">+ Add page</button>
                        </div>
                    @else
                        <p class="fd-hint">Drag elements to reorder. Click any block to configure it.</p>
                    @endif
                </div>

                <main class="fd-canvas">
                    @php
                        $headerPlacement = $this->pageChrome['header_placement'];
                        $footerPlacement = $this->pageChrome['footer_placement'];
                        $headerZoneOutside = \Spiggle\FormBuilder\Support\PageChrome::isOutsidePlacement($headerPlacement);
                        $footerZoneOutside = \Spiggle\FormBuilder\Support\PageChrome::isOutsidePlacement($footerPlacement);
                    @endphp
                    <div class="fd-canvas-form-shell">
                        @if ($headerZoneOutside)
                            @include('form-builder::filament.livewire.partials.form-designer-chrome-zone', [
                                'zone' => 'header',
                                'label' => 'Form header',
                                'wrapperClass' => 'fb-chrome-outside-above',
                                'emptyLabel' => '+ Add header — content, image, video, or rich text',
                            ])
                        @endif

                        <div @class([
                            'fd-canvas-sheet',
                            'fb-form-card',
                            'fb-has-chrome-bleed-header' => $this->pageChromeZoneBleeds('header') && ! $headerZoneOutside && $this->headerBlocks !== [],
                            'fb-has-chrome-bleed-footer' => $this->pageChromeZoneBleeds('footer') && ! $footerZoneOutside && $this->footerBlocks !== [],
                        ])>
                            @unless ($headerZoneOutside)
                                @include('form-builder::filament.livewire.partials.form-designer-chrome-zone', [
                                    'zone' => 'header',
                                    'label' => 'Form header',
                                    'bleed' => $this->pageChromeZoneBleeds('header'),
                                    'emptyLabel' => '+ Add header — content, image, video, or rich text',
                                ])
                            @endunless

                        <section
                            class="fd-fields-zone {{ $insertTarget === 'form' ? 'is-target' : '' }}"
                            @click="focusZone('form')"
                        >
                            <div class="fd-fields-divider">
                                <p class="fd-zone-label">Form fields</p>
                                @if ($containerType !== 'single')
                                    <p class="fd-hint fd-section-hint">
                                        Section: {{ $schema[$activeSection]['label'] ?? 'Details' }}
                                    </p>
                                @endif
                            </div>

                            <div class="fd-fields-grid-wrap">
                                @if (count($this->activeFieldItems) === 0)
                                    <div class="fd-empty-zone" aria-hidden="true">
                                        Add fields or inline content blocks from the palette.
                                    </div>
                                @endif

                                <div
                                    class="fd-grid fd-fields-grid"
                                    data-fd-zone="form"
                                    data-fd-sort-group="fd-form"
                                    wire:ignore.self
                                    wire:key="fd-zone-form"
                                    x-init="{{ $fdZoneInit }}"
                                >
                                    @foreach ($this->activeFieldItems as $item)
                                        @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [
                                            'item' => $item,
                                            'zone' => 'form',
                                        ])
                                    @endforeach
                                </div>                            </div>
                        </section>

                        @unless ($footerZoneOutside)
                            @include('form-builder::filament.livewire.partials.form-designer-chrome-zone', [
                                'zone' => 'footer',
                                'label' => 'Form footer',
                                'bleed' => $this->pageChromeZoneBleeds('footer'),
                                'emptyLabel' => '+ Add footer — legal text, links, or branding',
                            ])
                        @endunless
                        </div>

                        @if ($footerZoneOutside)
                            @include('form-builder::filament.livewire.partials.form-designer-chrome-zone', [
                                'zone' => 'footer',
                                'label' => 'Form footer',
                                'wrapperClass' => 'fb-chrome-outside-below',
                                'emptyLabel' => '+ Add footer — legal text, links, or branding',
                            ])
                        @endif
                    </div>
                </main>
            </div>
        </div>
    </div>

    {{-- Floating inspector — only in DOM when open so fixed overlay never blocks canvas --}}
    @if ($inspectorOpen)
    <div
        class="fd-slideover"
        @keydown.escape.window="$wire.closeInspector()"
    >
        <div
            class="fd-slideover-backdrop"
            wire:click="closeInspector"
            wire:loading.class="fd-no-pointer"
            wire:target="toggleSelectedMetaBool, toggleSelectedBoolean, updateSelected"
        ></div>
        <aside
            class="fd-slideover-panel"
            role="dialog"
            aria-label="Element inspector"
            wire:click.stop
            wire:key="fd-inspector-panel"
        >
            <header class="fd-slideover-header">
                <div>
                    <h3>{{ $this->selectedInspectorTitle() }}</h3>
                    <p>{{ $selectedId ? 'Configure the selected element' : 'Configure zone placement and content' }}</p>
                </div>
                <div class="fd-slideover-actions">
                    @if ($selectedId)
                        <button type="button" class="fd-icon-btn" wire:click="moveItem('{{ $selectedId }}', -1)" title="Move up">↑</button>
                        <button type="button" class="fd-icon-btn" wire:click="moveItem('{{ $selectedId }}', 1)" title="Move down">↓</button>
                    @endif
                    <button type="button" class="fd-icon-btn" wire:click="closeInspector" aria-label="Close">&times;</button>
                </div>
            </header>

            <div class="fd-panel-body" wire:click.stop>
                @if ($inspectorOpen && ($selectedId || $this->inspectorChromeZone()))
                    @if ($chromeZone = $this->inspectorChromeZone())
                        @include('form-builder::filament.livewire.partials.form-designer-inspector-chrome-zone', [
                            'chromeZone' => $chromeZone,
                        ])
                    @endif

                    @if ($selectedId)
                        @if ($this->inspectorChromeZone())
                            <div class="fd-inspector-divider" aria-hidden="true"></div>
                        @endif
                        @include('form-builder::filament.livewire.partials.form-designer-inspector')
                    @endif
                @else
                    <p class="fd-hint">Select an element on the canvas to edit its settings.</p>
                @endif
            </div>

            @if ($inspectorOpen && $selectedId)
                <footer class="fd-slideover-footer">
                    <button type="button" class="fd-btn fd-btn-primary fd-btn-block" wire:click="closeInspector">
                        Apply
                    </button>
                    <button type="button" class="fd-btn fd-btn-danger fd-btn-block" wire:click="removeSelected">
                        Remove element
                    </button>
                </footer>
            @endif
        </aside>
    </div>
    @endif

    <x-filament-actions::modals />
</div>

@script
<script>
    Alpine.data('formDesignerCanvas', (initialPanel = 'form', initialPaletteTab = 'fields') => ({
        panel: initialPanel,
        paletteTab: initialPaletteTab,
        isSorting: false,
        sortCommitTimer: null,
        stuckDragTimer: null,

        init() {
            this._mountedZones = [];
            this._remountTimer = null;

            this._sortClassObserver = new MutationObserver(() => {
                this.isSorting = document.body.classList.contains('sorting');
            });
            this._sortClassObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

            this._onEscape = (event) => {
                if (event.key === 'Escape') {
                    this.abortSort();
                }
            };
            this._onSafetyPointerUp = () => this.scheduleStuckDragCleanup();
            window.addEventListener('keydown', this._onEscape);
            window.addEventListener('pointerup', this._onSafetyPointerUp, true);
            window.addEventListener('pointercancel', this._onSafetyPointerUp, true);

            this._onLivewireInit = () => {
                window.Livewire.hook('commit', ({ component, succeed }) => {
                    if (! component?.el?.closest?.('.fd-root')) {
                        return;
                    }

                    succeed(() => {
                        this.scheduleRemountZones();
                    });
                });
            };

            if (window.Livewire) {
                this._onLivewireInit();
            } else {
                document.addEventListener('livewire:init', this._onLivewireInit, { once: true });
            }
        },

        destroy() {
            this._sortClassObserver?.disconnect();
            this._mountedZones?.forEach((el) => window.__spiggleFdUnmountZone?.(el));
            this._mountedZones = [];
            if (this._onLivewireInit) {
                document.removeEventListener('livewire:init', this._onLivewireInit);
            }
            window.removeEventListener('keydown', this._onEscape);
            window.removeEventListener('pointerup', this._onSafetyPointerUp, true);
            window.removeEventListener('pointercancel', this._onSafetyPointerUp, true);
            this.clearSortCommitTimer();
            this.clearStuckDragTimer();
            this.clearRemountTimer();
            this.cleanupDragArtifacts();
        },

        isDragLocked() {
            return this.isSorting
                || this._committingSort
                || document.body.classList.contains('sorting');
        },

        selectCanvasItem(id, zone) {
            if (this.isDragLocked()) {
                return;
            }

            $wire.selectItem(id, zone);
        },

        focusZone(target) {
            if (this.isDragLocked()) {
                return;
            }

            $wire.focusInsertTarget(target);
        },

        focusThankYouZone(target) {
            if (this.isDragLocked()) {
                return;
            }

            $wire.focusThankYouTarget(target);
        },

        mountZone(el) {
            if (! el || typeof window.__spiggleFdMountZone !== 'function') {
                return;
            }

            window.__spiggleFdMountZone(el, (itemId, zoneId, position) => {
                this.handleCanvasSort(zoneId, itemId, position);
            });

            this._mountedZones ??= [];
            if (! this._mountedZones.includes(el)) {
                this._mountedZones.push(el);
            }
        },

        scheduleRemountZones(force = false) {
            if (this.isDragLocked()) {
                return;
            }

            this.clearRemountTimer();
            this._remountTimer = window.setTimeout(() => {
                this._remountTimer = null;
                this.remountZones(force);
            }, 0);
        },

        clearRemountTimer() {
            if (this._remountTimer) {
                clearTimeout(this._remountTimer);
                this._remountTimer = null;
            }
        },

        remountZones(force = false) {
            if (this.isDragLocked()) {
                return;
            }

            const commit = (itemId, zoneId, position) => {
                this.handleCanvasSort(zoneId, itemId, position);
            };

            if (force && typeof window.__spiggleFdRemountZones === 'function') {
                window.__spiggleFdRemountZones(this.$root, commit);
            } else if (typeof window.__spiggleFdEnsureZones === 'function') {
                window.__spiggleFdEnsureZones(this.$root, commit);
            }

            this._mountedZones = Array.from(this.$root.querySelectorAll('[data-fd-zone]'));
        },

        handleCanvasSort(zone, item, position) {
            try {
                if (! item || ! zone || position === null || position === undefined) {
                    return;
                }

                const itemId = String(item);
                const zoneId = String(zone);
                const pos = Number(position);

                this.clearSortCommitTimer();
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        this.commitSort(itemId, zoneId, pos);
                    });
                });
            } catch {
                this.abortSort();
            }
        },

        commitSort(itemId, zoneId, position) {
            if (this._committingSort) {
                return;
            }

            this._committingSort = true;

            try {
                this.cleanupDragArtifacts();
                $wire.sortItem(itemId, zoneId, position);
            } finally {
                this._committingSort = false;
            }
        },

        clearSortCommitTimer() {
            if (this.sortCommitTimer) {
                clearTimeout(this.sortCommitTimer);
                this.sortCommitTimer = null;
            }
        },

        clearStuckDragTimer() {
            if (this.stuckDragTimer) {
                clearTimeout(this.stuckDragTimer);
                this.stuckDragTimer = null;
            }
        },

        abortSort() {
            this.clearSortCommitTimer();
            this.clearStuckDragTimer();
            this._committingSort = false;
            this.isSorting = false;

            if (typeof window.__spiggleFdForceEndSort === 'function') {
                window.__spiggleFdForceEndSort();
            } else {
                this.cleanupDragArtifacts();
            }
        },

        scheduleStuckDragCleanup() {
            this.clearStuckDragTimer();
            this.stuckDragTimer = window.setTimeout(() => {
                this.stuckDragTimer = null;

                if (! document.body.classList.contains('sorting')) {
                    return;
                }

                this.stuckDragTimer = window.setTimeout(() => {
                    this.stuckDragTimer = null;

                    if (document.body.classList.contains('sorting')) {
                        this.abortSort();
                    }
                }, 750);
            }, 50);
        },

        cleanupDragArtifacts() {
            document.body.classList.remove('sorting');
            document.body.style.removeProperty('user-select');
            document.querySelectorAll('.sortable-ghost, .sortable-chosen, .sortable-drag, .is-drop-target').forEach((node) => {
                node.classList.remove('sortable-ghost', 'sortable-chosen', 'sortable-drag', 'is-drop-target');
            });
            document.querySelectorAll('.sortable-fallback').forEach((node) => node.remove());
        },

        switchPanel(next) {
            if (this.panel === next) {
                return;
            }

            this.panel = next;

            if (next === 'thank_you') {
                this.paletteTab = 'content';
            } else if ($wire.insertTarget === 'form') {
                this.paletteTab = 'fields';
            }

            $wire.selectPanel(next);
        },

        switchPaletteTab(tab) {
            if (this.paletteTab === tab) {
                return;
            }

            this.paletteTab = tab;
            $wire.setPaletteTab(tab);
            this.scheduleRemountZones(true);
        },

        handleKeydown(event) {
            if (this.isEditableTarget(event.target)) {
                return;
            }

            const key = event.key.toLowerCase();
            const mod = event.ctrlKey || event.metaKey;

            if (mod && key === 'z' && ! event.shiftKey) {
                event.preventDefault();
                if ($wire.canUndo()) {
                    $wire.undo();
                }

                return;
            }

            if (mod && (key === 'y' || (key === 'z' && event.shiftKey))) {
                event.preventDefault();
                if ($wire.canRedo()) {
                    $wire.redo();
                }
            }
        },

        isEditableTarget(target) {
            if (! target || ! target.closest) {
                return false;
            }

            return target.closest('input, textarea, select, [contenteditable="true"], .fi-fo-rich-editor');
        },

        onPaletteClick(kind, type) {
            $wire.addFromPalette(kind, type);
        },
    }));
</script>
@endscript

