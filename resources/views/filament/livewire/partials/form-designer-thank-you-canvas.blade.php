@php
    $thankYouPreview = [
        'layout' => $thankYouLayout,
        'layout_meta' => $thankYouLayoutMeta,
        'blocks' => $thankYouBlocks,
        'header_blocks' => $thankYouHeaderBlocks,
        'show_form_name' => $thankYouShowFormName,
        'show_timestamp' => $thankYouShowTimestamp,
        'title' => $thankYouTitle,
        'message' => $thankYouMessage,
    ];
@endphp

<div class="fd-thank-card-editor">
    <div class="fd-thank-preview">
        @if ($thankYouLayout === 'review_card')
            <div
                class="fb-thankyou-review fd-thank-layout-preview"
                style="--fb-review-page-bg: {{ $thankYouLayoutMeta['page_background'] ?? '#3b82f6' }}; --fb-review-header-bg: {{ $thankYouLayoutMeta['header_band_color'] ?? '#a5b4fc' }}; --fb-review-cta-bg: {{ $thankYouLayoutMeta['cta_color'] ?? '#2563eb' }};"
            >
                <div class="fb-thankyou-review-shell">
                    <div class="fb-thankyou-review-card fd-thank-card-shell">
                        <div class="fb-thankyou-review-header-band" aria-hidden="true"></div>
                        <div class="fb-thankyou-review-hero">
                            @if (($thankYouLayoutMeta['hero_icon_type'] ?? 'emoji') === 'image' && ! empty($thankYouLayoutMeta['hero_image_url']))
                                <img src="{{ $thankYouLayoutMeta['hero_image_url'] }}" alt="" class="fb-thankyou-review-hero-img">
                            @else
                                <span class="fb-thankyou-review-hero-emoji" aria-hidden="true">{{ $thankYouLayoutMeta['hero_icon'] ?? '✉️' }}</span>
                            @endif
                        </div>
                        <div
                            class="fb-thankyou-review-body {{ $thankYouInsertTarget === 'body' ? 'is-target' : '' }}"
                            @click="focusThankYouZone('body')"
                        >
                            <div
                                class="fd-grid fd-thank-blocks-grid"
                                data-fd-zone="thank_you"
                                data-fd-sort-group="fd-thank-you"
                                wire:ignore.self
                                wire:key="fd-zone-thank_you_review"
                                x-init="{{ $fdZoneInit }}"
                                @click.stop="focusThankYouZone('body')"
                            >
                                @foreach ($thankYouBlocks as $block)
                                    @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [
                                        'item' => $block,
                                        'zone' => 'thank_you',
                                    ])
                                @endforeach
                            </div>
                            @if ($thankYouShowTimestamp)
                                <p class="fb-thankyou-time">Submitted on {{ now()->format('M j, Y \a\t g:i A') }}</p>
                            @endif
                            @if (! empty($thankYouLayoutMeta['cta_label']) && ! empty($thankYouLayoutMeta['cta_url']))
                                <span class="fb-thankyou-review-cta fb-thankyou-review-cta-preview">{{ $thankYouLayoutMeta['cta_label'] }}</span>
                            @endif
                            <span class="fb-thankyou-submit-another fb-thankyou-submit-preview">Submit Another Response</span>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($thankYouLayout === 'campaign')
            <div
                class="fb-thankyou-campaign fd-thank-layout-preview"
                style="--fb-campaign-page-bg: {{ $thankYouLayoutMeta['page_background'] ?? '#f8fafc' }}; --fb-campaign-success-bg: {{ $thankYouLayoutMeta['success_icon_color'] ?? '#10b981' }}; --fb-campaign-btn-bg: {{ $thankYouLayoutMeta['website_button_color'] ?? '#10b981' }};"
            >
                <div class="fb-thankyou-campaign-accents" aria-hidden="true">
                    <span style="background: {{ $thankYouLayoutMeta['accent_colors'][0] ?? '#f5b800' }}"></span>
                    <span style="background: {{ $thankYouLayoutMeta['accent_colors'][1] ?? '#10b981' }}"></span>
                </div>
                <div class="fb-thankyou-campaign-inner fd-thank-card-shell">
                    <div class="fb-thankyou-check fb-thankyou-check-lg" aria-hidden="true">{{ $thankYouLayoutMeta['success_icon'] ?? '✓' }}</div>
                    <div
                        class="fb-thankyou-campaign-copy {{ $thankYouInsertTarget === 'body' ? 'is-target' : '' }}"
                        @click="focusThankYouZone('body')"
                    >
                        <div
                            class="fd-grid fd-thank-blocks-grid"
                            data-fd-zone="thank_you"
                            data-fd-sort-group="fd-thank-you"
                            wire:ignore.self
                            wire:key="fd-zone-thank_you_campaign"
                            x-init="{{ $fdZoneInit }}"
                            @click.stop="focusThankYouZone('body')"
                        >
                            @foreach ($thankYouBlocks as $block)
                                @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [
                                    'item' => $block,
                                    'zone' => 'thank_you',
                                ])
                            @endforeach
                        </div>
                    </div>
                    @php
                        $previewSocialLinks = array_values(array_filter(
                            $thankYouLayoutMeta['social_links'] ?? [],
                            fn (mixed $link): bool => is_array($link) && trim((string) ($link['url'] ?? '')) !== '',
                        ));
                        $previewWebsiteUrl = trim((string) ($thankYouLayoutMeta['website_button_url'] ?? ''));
                    @endphp
                    @if ($previewSocialLinks !== [] || $previewWebsiteUrl !== '')
                        <div class="fb-thankyou-campaign-cards">
                            @if ($previewSocialLinks !== [])
                                <div class="fb-thankyou-campaign-card">
                                    <h3 class="fb-thankyou-campaign-card-title">{{ $thankYouLayoutMeta['connect_card_title'] ?? 'Connect With Us' }}</h3>
                                    <div class="fb-thankyou-campaign-social">
                                        @foreach ($previewSocialLinks as $link)
                                            <span class="fb-thankyou-campaign-social-link is-{{ $link['platform'] ?? 'link' }}">{{ ucfirst($link['platform'] ?? 'link') }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if ($previewWebsiteUrl !== '')
                                <div class="fb-thankyou-campaign-card">
                                    <h3 class="fb-thankyou-campaign-card-title">{{ $thankYouLayoutMeta['website_card_title'] ?? 'Visit Our Website' }}</h3>
                                    <span class="fb-thankyou-campaign-btn fb-thankyou-campaign-btn-preview">{{ $thankYouLayoutMeta['website_button_label'] ?? 'Visit Website' }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if ($thankYouShowTimestamp)
                        <p class="fb-thankyou-time">Submitted on {{ now()->format('M j, Y \a\t g:i A') }}</p>
                    @endif
                    <span class="fb-thankyou-submit-another fb-thankyou-submit-preview">Submit Another Response</span>
                </div>
            </div>
        @else
            <div class="fb-thankyou-card fd-thank-card-shell">
                @if ($thankYouShowFormName)
                    <div
                        class="fb-thankyou-header {{ $thankYouInsertTarget === 'header' ? 'is-target' : '' }}"
                        @click="focusThankYouZone('header')"
                    >
                        <div class="fb-thankyou-check" aria-hidden="true">✓</div>
                        <div
                            class="fd-grid fd-thank-header-blocks-grid"
                            data-fd-zone="thank_you_header"
                            data-fd-sort-group="fd-thank-you"
                            wire:ignore.self
                            wire:key="fd-zone-thank_you_header"
                            x-init="{{ $fdZoneInit }}"
                            @click.stop="focusThankYouZone('header')"
                        >
                            @foreach ($thankYouHeaderBlocks as $block)
                                @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [
                                    'item' => $block,
                                    'zone' => 'thank_you_header',
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif
                <div
                    class="fb-thankyou-body {{ $thankYouInsertTarget === 'body' ? 'is-target' : '' }}"
                    @click="focusThankYouZone('body')"
                >
                    <div
                        class="fd-grid fd-thank-blocks-grid"
                        data-fd-zone="thank_you"
                        data-fd-sort-group="fd-thank-you"
                        wire:ignore.self
                        wire:key="fd-zone-thank_you"
                        x-init="{{ $fdZoneInit }}"
                        @click.stop="focusThankYouZone('body')"
                    >
                        @foreach ($thankYouBlocks as $block)
                            @include('form-builder::filament.livewire.partials.form-designer-canvas-item', [
                                'item' => $block,
                                'zone' => 'thank_you',
                            ])
                        @endforeach
                    </div>
                    @if ($thankYouShowTimestamp)
                        <p class="fb-thankyou-time">Submitted on {{ now()->format('M j, Y \a\t g:i A') }}</p>
                    @endif
                    <span class="fb-thankyou-submit-another fb-thankyou-submit-preview">Submit Another Response</span>
                </div>
            </div>
        @endif
    </div>
</div>
