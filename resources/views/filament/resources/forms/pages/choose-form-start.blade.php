<x-filament-panels::page>
    <div @class(['cfs-wizard', 'cfs-wizard--wide' => $step === 2])>

        @if ($step === 1)

            <header class="cfs-header">
                <div class="cfs-header-text">
                    <h2 class="cfs-title">Create form</h2>
                    <p class="cfs-subtitle">Start from scratch or pick a pre-built template.</p>
                </div>
                <span class="cfs-step">1/3</span>
            </header>

            <div class="cfs-start-grid">
                <button type="button" wire:click="createBlank" class="cfs-start-card">
                    <span class="cfs-start-icon" aria-hidden="true">＋</span>
                    <strong>Blank form</strong>
                    <span>Start from scratch with an empty canvas.</span>
                </button>

                <button type="button" wire:click="showTemplates" class="cfs-start-card">
                    <span class="cfs-start-icon" aria-hidden="true">▦</span>
                    <strong>Template</strong>
                    <span>Pick a pre-built design and customize it.</span>
                </button>
            </div>

        @elseif ($step === 2)

            <header class="cfs-header">
                <div class="cfs-header-text">
                    <h2 class="cfs-title">Choose category</h2>
                    <p class="cfs-subtitle">Browse {{ count(\Spiggle\FormBuilder\Support\FormTemplates::all()) }} templates organized by purpose.</p>
                </div>
                <span class="cfs-step">2/3</span>
            </header>

            <div class="cfs-category-grid">
                @foreach ($this->templatesByCategory() as $categoryKey => $group)
                    <button
                        type="button"
                        wire:click="selectCategory('{{ $categoryKey }}')"
                        wire:key="category-{{ $categoryKey }}"
                        class="cfs-category-card"
                    >
                        <span class="cfs-category-icon">
                            {!! \Spiggle\FormBuilder\Support\PaletteCatalog::iconSvg($group['category']['icon'] ?? 'document-text') !!}
                        </span>
                        <span class="cfs-category-label">{{ $group['category']['label'] }}</span>
                        <span class="cfs-category-count">{{ count($group['templates']) }} templates</span>
                    </button>
                @endforeach
            </div>

            <footer class="cfs-footer">
                <button type="button" wire:click="goBack" class="cfs-btn cfs-btn--ghost">Cancel</button>
            </footer>

        @else

            <header class="cfs-header">
                <div class="cfs-header-text">
                    <h2 class="cfs-title">Choose template</h2>
                    <p class="cfs-subtitle">
                        {{ $this->selectedCategoryLabel() }} — select a template to start organizing your content.
                    </p>
                </div>
                <span class="cfs-step">3/3</span>
            </header>

            <input
                type="search"
                wire:model.live.debounce.300ms="searchQuery"
                class="cfs-search"
                placeholder="Search templates in this category…"
                autocomplete="off"
            />

            <div class="cfs-template-list">
                @forelse ($this->filteredTemplates() as $template)
                    @php
                        $containerType = (string) ($template['definition']['container_type'] ?? 'single');
                        $isPro = ($template['tier'] ?? 'core') === 'pro';
                    @endphp

                    <button
                        type="button"
                        wire:click="selectTemplate('{{ $template['slug'] }}')"
                        wire:key="template-{{ $template['slug'] }}"
                        @class([
                            'cfs-template-card',
                            'is-selected' => $selectedTemplate === $template['slug'],
                        ])
                    >
                        <div class="cfs-preview" aria-hidden="true">
                            {!! \Spiggle\FormBuilder\Support\LayoutPreview::svg($containerType) !!}
                        </div>

                        <div class="cfs-template-info">
                            <strong>{{ $template['name'] }}</strong>
                            <span>{{ $template['description'] }}</span>
                        </div>

                        @if ($isPro)
                            <span class="cfs-pro-badge">PRO</span>
                        @endif
                    </button>
                @empty
                    <p class="cfs-empty">No templates match your search.</p>
                @endforelse
            </div>

            <footer class="cfs-footer">
                <button type="button" wire:click="goBack" class="cfs-btn cfs-btn--ghost">Cancel</button>
                <button
                    type="button"
                    wire:click="confirmTemplate"
                    class="cfs-btn cfs-btn--primary"
                    @disabled($selectedTemplate === null)
                >
                    Next
                </button>
            </footer>

        @endif

    </div>

    @include('form-builder::filament.resources.forms.pages.partials.choose-form-start-styles')
</x-filament-panels::page>
