<x-filament-panels::page :full-width="true">
    @push('styles')
        @if (class_exists(\Filament\Support\Facades\FilamentAsset::class))
            <link rel="stylesheet" href="{{ \Filament\Support\Facades\FilamentAsset::getStyleHref('form-designer') }}" data-navigate-track />
        @endif
    @endpush

    {{-- Load Sortable before the designer DOM so zones mount on first init --}}
    @if (class_exists(\Filament\Support\Facades\FilamentAsset::class))
        <script
            src="{{ \Filament\Support\Facades\FilamentAsset::getScriptSrc('form-designer-sort') }}"
            data-navigate-track
        ></script>
    @endif

    @livewire(\Spiggle\FormBuilder\Filament\Livewire\FormDesigner::class, ['formId' => $this->getRecord()->id], key('form-designer-'.$this->getRecord()->id))
</x-filament-panels::page>
