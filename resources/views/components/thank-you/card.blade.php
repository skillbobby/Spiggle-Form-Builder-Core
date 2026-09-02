@php
    $preview = (bool) ($preview ?? false);
    $thankYou = $thankYou ?? [];
    $title = $thankYou['title'] ?? 'Thank you!';
    $message = $thankYou['message'] ?? 'Your response has been received and saved.';
    $blocks = $thankYou['blocks'] ?? [];
    $headerBlocks = $thankYou['header_blocks'] ?? [];
    $showName = (bool) ($thankYou['show_form_name'] ?? true);
    $showTimestamp = (bool) ($thankYou['show_timestamp'] ?? true);
    $submitLabel = $thankYou['submit_another_label'] ?? 'Submit Another Response';
    $timestamp = $submittedAt ?? null;
@endphp

<div class="fb-thankyou-card {{ $preview ? 'fb-thankyou-preview' : '' }}">
    @if ($showName && ($headerBlocks !== [] || ! empty($formName)))
        <div class="fb-thankyou-header">
            <div class="fb-thankyou-check" aria-hidden="true">✓</div>
            @if ($headerBlocks !== [])
                <div class="fb-thankyou-header-blocks">
                    @foreach ($headerBlocks as $block)
                        @include('form-builder::components.content-block', [
                            'block' => $block,
                            'preview' => $preview,
                        ])
                    @endforeach
                </div>
            @elseif (! empty($formName))
                <p class="fb-thankyou-form-name">{{ $formName }}</p>
            @endif
        </div>
    @endif
    <div class="fb-thankyou-body">
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
            <p class="fb-thankyou-message">{{ $message }}</p>
        @endif

        @if ($showTimestamp && $timestamp)
            <p class="fb-thankyou-time">Submitted on {{ $timestamp }}</p>
        @elseif ($showTimestamp && $preview)
            <p class="fb-thankyou-time">Submitted on {{ now()->format('M j, Y \a\t g:i A') }}</p>
        @endif

        @if (! $preview)
            <button type="button" class="fb-thankyou-submit-another" wire:click="submitAnother">{{ $submitLabel }}</button>
        @else
            <span class="fb-thankyou-submit-another fb-thankyou-submit-preview">{{ $submitLabel }}</span>
        @endif

        @if (! empty($redirectUrl))
            <p class="fb-thankyou-redirect"><a href="{{ $redirectUrl }}">Continue</a></p>
        @endif
    </div>
</div>
