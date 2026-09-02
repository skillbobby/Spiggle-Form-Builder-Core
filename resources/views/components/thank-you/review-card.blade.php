@php
    $preview = (bool) ($preview ?? false);
    $thankYou = $thankYou ?? [];
    $meta = \Spiggle\FormBuilder\Support\ThankYouLayouts::normalizeMeta(
        'review_card',
        is_array($thankYou['layout_meta'] ?? null) ? $thankYou['layout_meta'] : [],
    );
    $blocks = $thankYou['blocks'] ?? [];
    $title = $thankYou['title'] ?? 'Thank you!';
    $message = $thankYou['message'] ?? '';
    $showTimestamp = (bool) ($thankYou['show_timestamp'] ?? true);
    $submitLabel = $thankYou['submit_another_label'] ?? 'Submit Another Response';
    $timestamp = $submittedAt ?? null;
    $heroType = (string) ($meta['hero_icon_type'] ?? 'emoji');
    $heroImage = trim((string) ($meta['hero_image_url'] ?? ''));
    $ctaLabel = trim((string) ($meta['cta_label'] ?? ''));
    $ctaUrl = trim((string) ($meta['cta_url'] ?? ''));
@endphp

<div
    class="fb-thankyou-review {{ $preview ? 'fb-thankyou-preview' : '' }}"
    style="--fb-review-page-bg: {{ $meta['page_background'] ?? '#3b82f6' }}; --fb-review-header-bg: {{ $meta['header_band_color'] ?? '#a5b4fc' }}; --fb-review-cta-bg: {{ $meta['cta_color'] ?? '#2563eb' }};"
>
    <div class="fb-thankyou-review-shell">
        <div class="fb-thankyou-review-card">
            <div class="fb-thankyou-review-header-band" aria-hidden="true"></div>

            <div class="fb-thankyou-review-hero">
                @if ($heroType === 'image' && $heroImage !== '')
                    <img src="{{ $heroImage }}" alt="" class="fb-thankyou-review-hero-img">
                @else
                    <span class="fb-thankyou-review-hero-emoji" aria-hidden="true">{{ $meta['hero_icon'] ?? '✉️' }}</span>
                @endif
            </div>

            <div class="fb-thankyou-review-body">
                @if ($blocks !== [])
                    <div class="fb-thankyou-content-blocks">
                        @foreach ($blocks as $block)
                            @include('form-builder::components.content-block', [
                                'block' => $block,
                                'preview' => $preview,
                            ])
                        @endforeach
                    </div>
                @else
                    <h2 class="fb-thankyou-title">{{ $title }}</h2>
                    @if ($message)
                        <p class="fb-thankyou-message">{{ $message }}</p>
                    @endif
                @endif

                @if ($showTimestamp && $timestamp)
                    <p class="fb-thankyou-time">Submitted on {{ $timestamp }}</p>
                @elseif ($showTimestamp && $preview)
                    <p class="fb-thankyou-time">Submitted on {{ now()->format('M j, Y \a\t g:i A') }}</p>
                @endif

                @if ($ctaLabel !== '' && $ctaUrl !== '')
                    @if ($preview)
                        <span class="fb-thankyou-review-cta fb-thankyou-review-cta-preview">{{ $ctaLabel }}</span>
                    @else
                        <a href="{{ $ctaUrl }}" class="fb-thankyou-review-cta" target="_blank" rel="noopener">{{ $ctaLabel }}</a>
                    @endif
                @endif

                @if (! $preview)
                    <button type="button" class="fb-thankyou-submit-another fb-thankyou-review-submit-another" wire:click="submitAnother">{{ $submitLabel }}</button>
                @endif

                @if (! empty($redirectUrl))
                    <p class="fb-thankyou-redirect"><a href="{{ $redirectUrl }}">Continue</a></p>
                @endif
            </div>
        </div>
    </div>
</div>
