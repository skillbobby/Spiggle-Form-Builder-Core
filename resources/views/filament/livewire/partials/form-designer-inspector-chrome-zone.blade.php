@php
    $zone = $chromeZone ?? 'header';
    $placement = $zone === 'header'
        ? $this->pageChrome['header_placement']
        : $this->pageChrome['footer_placement'];
    $placementLabels = $zone === 'header'
        ? \Spiggle\FormBuilder\Support\PageChrome::headerPlacementLabels()
        : \Spiggle\FormBuilder\Support\PageChrome::footerPlacementLabels();
    $placementAction = $zone === 'header' ? 'updateHeaderPlacement' : 'updateFooterPlacement';
@endphp

<label class="fd-field">
    <span>Placement</span>
    <select class="fd-input" wire:change="{{ $placementAction }}($event.target.value)">
        @foreach ($placementLabels as $value => $optionLabel)
            <option value="{{ $value }}" @selected($placement === $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</label>
