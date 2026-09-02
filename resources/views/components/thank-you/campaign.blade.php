@php
    $preview = (bool) ($preview ?? false);
    $thankYou = $thankYou ?? [];
    $meta = \Spiggle\FormBuilder\Support\ThankYouLayouts::normalizeMeta(
        'campaign',
        is_array($thankYou['layout_meta'] ?? null) ? $thankYou['layout_meta'] : [],
    );
    $blocks = $thankYou['blocks'] ?? [];
    $title = $thankYou['title'] ?? 'Thank you!';
    $message = $thankYou['message'] ?? '';
    $accents = $meta['accent_colors'] ?? ['#f5b800', '#10b981'];
    $submitLabel = $thankYou['submit_another_label'] ?? 'Submit Another Response';
    $showTimestamp = (bool) ($thankYou['show_timestamp'] ?? true);
    $timestamp = $submittedAt ?? null;
    $socialLinks = is_array($meta['social_links'] ?? null) ? $meta['social_links'] : [];
    $socialLinksWithUrls = array_values(array_filter($socialLinks, function (mixed $link): bool {
        return is_array($link) && trim((string) ($link['url'] ?? '')) !== '';
    }));
    $websiteUrl = trim((string) ($meta['website_button_url'] ?? ''));
    $websiteLabel = trim((string) ($meta['website_button_label'] ?? 'Visit Website'));
    $platformLabels = [
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
    ];
@endphp

<div
    class="fb-thankyou-campaign {{ $preview ? 'fb-thankyou-preview' : '' }}"
    style="--fb-campaign-page-bg: {{ $meta['page_background'] ?? '#f8fafc' }}; --fb-campaign-success-bg: {{ $meta['success_icon_color'] ?? '#10b981' }}; --fb-campaign-btn-bg: {{ $meta['website_button_color'] ?? '#10b981' }};"
>
    <div class="fb-thankyou-campaign-accents" aria-hidden="true">
        <span style="background: {{ $accents[0] ?? '#f5b800' }}"></span>
        <span style="background: {{ $accents[1] ?? '#10b981' }}"></span>
    </div>
    <div class="fb-thankyou-campaign-inner">
        <div class="fb-thankyou-check fb-thankyou-check-lg" aria-hidden="true">{{ $meta['success_icon'] ?? '✓' }}</div>

        @if ($blocks !== [])
            <div class="fb-thankyou-content-blocks fb-thankyou-campaign-copy">
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

        @if ($socialLinksWithUrls !== [] || $websiteUrl !== '')
            <div class="fb-thankyou-campaign-cards">
                @if ($socialLinksWithUrls !== [])
                    <div class="fb-thankyou-campaign-card">
                        <h3 class="fb-thankyou-campaign-card-title">{{ $meta['connect_card_title'] ?? 'Connect With Us' }}</h3>
                        <div class="fb-thankyou-campaign-social">
                            @foreach ($socialLinksWithUrls as $link)
                                @php
                                    $platform = (string) ($link['platform'] ?? 'link');
                                    $url = trim((string) ($link['url'] ?? ''));
                                    $label = $platformLabels[$platform] ?? ucfirst($platform);
                                @endphp
                                @if ($preview)
                                    <span class="fb-thankyou-campaign-social-link is-{{ $platform }}" aria-hidden="true">{{ $label }}</span>
                                @else
                                    <a href="{{ $url }}" class="fb-thankyou-campaign-social-link is-{{ $platform }}" target="_blank" rel="noopener" aria-label="{{ $label }}">{{ $label }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($websiteUrl !== '')
                    <div class="fb-thankyou-campaign-card">
                        <h3 class="fb-thankyou-campaign-card-title">{{ $meta['website_card_title'] ?? 'Visit Our Website' }}</h3>
                        @if ($preview)
                            <span class="fb-thankyou-campaign-btn fb-thankyou-campaign-btn-preview">{{ $websiteLabel }}</span>
                        @else
                            <a href="{{ $websiteUrl }}" class="fb-thankyou-campaign-btn" target="_blank" rel="noopener">{{ $websiteLabel }}</a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if ($showTimestamp && $timestamp)
            <p class="fb-thankyou-time">Submitted on {{ $timestamp }}</p>
        @elseif ($showTimestamp && $preview)
            <p class="fb-thankyou-time">Submitted on {{ now()->format('M j, Y \a\t g:i A') }}</p>
        @endif

        @if (! $preview)
            <button type="button" class="fb-thankyou-submit-another" wire:click="submitAnother">{{ $submitLabel }}</button>
        @endif

        @if (! empty($redirectUrl))
            <p class="fb-thankyou-redirect"><a href="{{ $redirectUrl }}">Continue</a></p>
        @endif
    </div>
</div>
