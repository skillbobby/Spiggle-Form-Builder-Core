<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="fi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @filamentStyles
    @php
        try {
            echo \Filament\Facades\Filament::getDefaultPanel()->getTheme()?->getHtml();
        } catch (\Throwable) {
        }
    @endphp
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --fb-bg: #f4f7fb;
            --fb-card: #ffffff;
            --fb-text: #1f2937;
            --fb-muted: #6b7280;
            --fb-border: #e5e7eb;
            --fb-primary: #f59e0b;
            --fb-primary-dark: #d97706;
            --fb-danger: #dc2626;
            --fb-radius: 14px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: radial-gradient(1200px 400px at 10% -10%, #fde68a55, transparent), var(--fb-bg);
            color: var(--fb-text);
            min-height: 100vh;
        }
        .fb-wrap { max-width: 880px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
        .fb-card {
            --fb-card-pad: 1.25rem;
            background: var(--fb-card);
            border: 1px solid var(--fb-border);
            border-radius: var(--fb-radius);
            box-shadow: 0 10px 40px rgba(15, 23, 42, .06);
            padding: var(--fb-card-pad);
        }
        @media (min-width: 768px) {
            .fb-card { --fb-card-pad: 2rem; }
        }
        .fb-form-card.fb-has-chrome-bleed-header,
        .fb-form-card.fb-has-chrome-bleed-footer {
            overflow: hidden;
        }
        .fb-chrome-outside-above { margin-bottom: 1rem; }
        .fb-chrome-outside-below { margin-top: 1rem; }
        .fb-chrome-zone { width: 100%; }
        .fb-chrome-header { margin-bottom: 1rem; }
        .fb-chrome-footer { margin-top: 1rem; }
        .fb-chrome-bleed {
            margin-inline: calc(-1 * var(--fb-card-pad));
            width: calc(100% + (2 * var(--fb-card-pad)));
        }
        .fb-chrome-header.fb-chrome-bleed {
            margin-top: calc(-1 * var(--fb-card-pad));
            margin-bottom: 1.25rem;
        }
        .fb-chrome-footer.fb-chrome-bleed {
            margin-bottom: calc(-1 * var(--fb-card-pad));
            margin-top: 1.25rem;
        }
        .fb-chrome-block-bleed {
            margin-inline: calc(-1 * var(--fb-card-pad));
            width: calc(100% + (2 * var(--fb-card-pad)));
            max-width: none;
        }
        .fb-chrome-block-bleed .fb-content-banner,
        .fb-chrome-block-bleed .fb-content-image-img,
        .fb-chrome-block-bleed .fb-content-video {
            border-radius: 0;
        }
        .fb-chrome-bleed .fb-content-banner,
        .fb-chrome-zone.fb-chrome-bleed .fb-content-banner {
            border-radius: 0;
        }
        .fb-has-chrome-bleed-header .fb-chrome-header.fb-chrome-bleed .fb-content-banner:first-child,
        .fb-has-chrome-bleed-header .fb-chrome-header.fb-chrome-bleed .fb-content:first-child .fb-content-banner {
            border-top-left-radius: calc(var(--fb-radius) - 1px);
            border-top-right-radius: calc(var(--fb-radius) - 1px);
        }
        .fb-title { font-size: 1.6rem; margin: 0 0 .35rem; letter-spacing: -.02em; }
        .fb-desc { color: var(--fb-muted); margin: 0 0 1.25rem; }
        .fb-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 1rem; }
        [class*="fb-span-"] { grid-column: span 12 / span 12; }
        @media (min-width: 768px) {
            .fb-span-1 { grid-column: span 1; } .fb-span-2 { grid-column: span 2; }
            .fb-span-3 { grid-column: span 3; } .fb-span-4 { grid-column: span 4; }
            .fb-span-5 { grid-column: span 5; } .fb-span-6 { grid-column: span 6; }
            .fb-span-7 { grid-column: span 7; } .fb-span-8 { grid-column: span 8; }
            .fb-span-9 { grid-column: span 9; } .fb-span-10 { grid-column: span 10; }
            .fb-span-11 { grid-column: span 11; } .fb-span-12 { grid-column: span 12; }
        }
        .fb-field { display: flex; flex-direction: column; gap: .35rem; }
        .fb-field.inline { flex-direction: row; align-items: center; gap: .75rem; }
        .fb-field.inline label { min-width: 8rem; }
        .fb-field.below { flex-direction: column-reverse; }
        .fb-label { font-weight: 600; font-size: .9rem; }
        .fb-req { color: var(--fb-danger); }
        .fb-hint { font-size: .8rem; color: var(--fb-muted); }
        .fb-error { font-size: .8rem; color: var(--fb-danger); }
        .fb-field.is-invalid .fb-input,
        .fb-field.is-invalid .fb-select,
        .fb-field.is-invalid .fb-textarea,
        .fb-field.is-invalid .fb-rich,
        .fb-field.is-invalid .fb-tags {
            border-color: var(--fb-danger);
            background: #fef2f2;
        }
        .fb-alert {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 10px;
            padding: .85rem 1rem;
            margin: 0 0 1.25rem;
        }
        .fb-alert ul { margin: .5rem 0 0; padding-left: 1.1rem; }
        .fb-tags {
            display: flex; flex-wrap: wrap; gap: .4rem; align-items: center;
            border: 1px solid var(--fb-border); border-radius: 10px; padding: .4rem .5rem; background: #fff;
        }
        .fb-tag {
            display: inline-flex; align-items: center; gap: .25rem;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 999px;
            padding: .15rem .55rem .15rem .7rem; font-size: .8rem; font-weight: 600;
        }
        .fb-tag.is-colored {
            background: color-mix(in srgb, var(--fb-option-color) 18%, #fff);
            border-color: color-mix(in srgb, var(--fb-option-color) 45%, #fff);
            color: var(--fb-option-color);
        }
        .fb-tag-x {
            border: 0; background: transparent; cursor: pointer; font-size: 1rem; line-height: 1; color: #92400e;
        }
        .fb-tag.is-colored .fb-tag-x { color: inherit; }
        .fb-tag-input {
            flex: 1 1 8rem; min-width: 8rem; border: 0; outline: none; font: inherit; padding: .35rem .25rem;
        }
        .fb-rich .fi-sc-field-label,
        .fb-rich .fi-fo-field-wrp-label,
        .fb-rich .fi-sc-field-error-message,
        .fb-rich .fi-fo-field-wrp-error-message { display: none; }
        .fb-content { margin-bottom: .25rem; width: 100%; min-width: 0; }
        .fb-content-banner {
            width: 100%;
            min-width: 100%;
        }
        .fb-accordion-item { margin-bottom: .65rem; }
        .fb-accordion-item:last-child { margin-bottom: 0; }
        .fb-acc-h { margin: 0; font-size: 1rem; }
        .fb-acc-btn {
            width: 100%; display: flex; justify-content: space-between; align-items: center; gap: .75rem;
            border: 1px solid var(--fb-border); background: #fff; border-radius: 10px;
            padding: .75rem 1rem; font: inherit; font-weight: 600; cursor: pointer; text-align: left;
            transition: border-color .2s ease, background-color .2s ease, border-radius .2s ease;
        }
        .fb-accordion-item.is-open .fb-acc-btn {
            border-color: var(--fb-primary);
            background: #fffbeb;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            border-bottom-color: color-mix(in srgb, var(--fb-primary) 35%, var(--fb-border));
        }
        .fb-acc-icon {
            width: .55rem; height: .55rem; flex-shrink: 0;
            border-right: 2px solid currentColor; border-bottom: 2px solid currentColor;
            transform: rotate(45deg); opacity: .55;
            transition: transform .2s ease, opacity .2s ease;
        }
        .fb-accordion-item.is-open .fb-acc-icon {
            transform: rotate(-135deg) translateY(-1px);
            opacity: .75;
        }
        .fb-acc-panel {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            border: 1px solid transparent;
            border-top: 0;
            border-radius: 0 0 10px 10px;
            background: #fff;
            overflow: hidden;
            transition: grid-template-rows .22s ease, opacity .2s ease, border-color .2s ease;
        }
        .fb-accordion-item.is-open .fb-acc-panel {
            grid-template-rows: 1fr;
            opacity: 1;
            border-color: var(--fb-primary);
        }
        .fb-acc-panel-inner { overflow: hidden; padding: 0 1rem; }
        .fb-accordion-item.is-open .fb-acc-panel-inner { padding: 1rem 1rem 1.15rem; }
        .fb-acc-panel-inner .fb-desc { margin-top: 0; }
        .fb-tab-panel { animation: fb-fade-in .2s ease; padding-top: 1.25rem; }
        .fb-tab-panel.is-active { display: block; }
        .fb-tab-panel:not(.is-active) { display: none; }
        @keyframes fb-fade-in {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fb-input, .fb-select, .fb-textarea, .fb-file {
            width: 100%;
            border: 1px solid var(--fb-border);
            border-radius: 10px;
            padding: .65rem .75rem;
            font: inherit;
            background: #fff;
        }
        .fb-select[multiple] { min-height: 6.5rem; padding: .5rem; }
        .fb-select[multiple] option { padding: .35rem .5rem; border-radius: 6px; }
        .fb-input:focus, .fb-select:focus, .fb-textarea:focus {
            outline: 2px solid #fde68a;
            border-color: var(--fb-primary);
        }
        .fb-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.25rem; }
        .fb-btn {
            border: 0; border-radius: 10px; padding: .7rem 1.1rem; font: inherit; font-weight: 600;
            cursor: pointer; background: var(--fb-primary); color: #111827;
        }
        .fb-btn:hover { background: var(--fb-primary-dark); color: #fff; }
        .fb-btn.secondary { background: #e5e7eb; color: #111827; }
        .fb-progress { height: 8px; background: #f3f4f6; border-radius: 99px; overflow: hidden; margin-bottom: 1rem; }
        .fb-progress > span { display: block; height: 100%; background: var(--fb-primary); }
        .fb-pills { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1rem; }
        .fb-pill {
            border: 1px solid var(--fb-border); background: #fff; border-radius: 999px;
            padding: .35rem .8rem; cursor: pointer; font-size: .85rem;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .fb-pill:hover { border-color: color-mix(in srgb, var(--fb-primary) 45%, var(--fb-border)); }
        .fb-pill[aria-selected="true"] {
            background: #fffbeb; border-color: var(--fb-primary); font-weight: 600;
            box-shadow: 0 1px 2px rgb(245 158 11 / 0.12);
        }
        .fb-success { text-align: center; padding: 2rem 1rem; }
        .fb-radio, .fb-check { display: flex; flex-direction: column; gap: .5rem; }
        .fb-radio label, .fb-check label {
            font-weight: 400; display: flex; gap: .55rem; align-items: flex-start;
            cursor: pointer; line-height: 1.35;
        }
        .fb-radio label.is-colored { color: var(--fb-option-color); font-weight: 600; }
        .fb-option-swatch {
            width: .7rem; height: .7rem; border-radius: 999px; flex-shrink: 0;
            background: var(--fb-option-color); margin-top: .25rem;
        }
        .fb-radio input, .fb-check input {
            margin-top: .2rem; accent-color: var(--fb-primary); width: 1rem; height: 1rem; flex-shrink: 0;
        }
        .fb-field-toggle { gap: .5rem; }
        .fb-toggle {
            display: inline-flex; align-items: center; gap: .65rem; cursor: pointer;
            font-weight: 500; user-select: none;
        }
        .fb-toggle-input {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }
        .fb-toggle-track {
            position: relative; display: inline-flex; flex-shrink: 0;
            width: 2.25rem; height: 1.25rem; border-radius: 999px;
            background: #d1d5db; transition: background-color .2s ease;
        }
        .fb-toggle-thumb {
            position: absolute; top: .125rem; left: .125rem;
            width: 1rem; height: 1rem; border-radius: 999px; background: #fff;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.18);
            transition: transform .2s ease;
        }
        .fb-toggle-input:checked + .fb-toggle-track { background: var(--fb-primary); }
        .fb-toggle-input:checked + .fb-toggle-track .fb-toggle-thumb { transform: translateX(1rem); }
        .fb-toggle-input:focus-visible + .fb-toggle-track {
            outline: 2px solid #fde68a; outline-offset: 2px;
        }
        .fb-toggle-label { line-height: 1.35; }
        .fb-hidden { display: none; }
        .fb-content-heading { margin: 0; }
        .fb-content-paragraph { margin: 0; line-height: 1.5; }
        .fb-content-divider { border: 0; border-top: 1px solid var(--fb-border); margin: .5rem 0; }
        .fb-content-banner {
            border-radius: 10px; overflow: hidden; position: relative;
            display: flex; align-items: center; justify-content: center;
            width: 100%; min-width: 100%;
            background: #f3f4f6;
        }
        .fb-content-banner.has-image { background: #111827; }
        .fb-content-banner-img {
            position: absolute; inset: 0; width: 100%; height: 100%;
            object-fit: cover; display: block;
        }
        .fb-content-banner-placeholder {
            font-size: .85rem; font-weight: 600; color: #6b7280; z-index: 1;
        }
        .fb-content-banner-caption {
            position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
            padding: .5rem .75rem; font-size: .8rem; color: #fff;
            background: linear-gradient(transparent, rgb(0 0 0 / 0.55));
        }
        .fb-content-image { margin: 0; }
        .fb-content-image-img {
            max-width: 100%; height: auto; border-radius: 10px; display: inline-block;
        }
        .fb-content-image-placeholder {
            display: flex; align-items: center; justify-content: center;
            min-height: 8rem; border: 1px dashed var(--fb-border); border-radius: 10px;
            color: var(--fb-muted); font-size: .85rem; background: #f9fafb;
        }
        .fb-content-image figcaption {
            margin-top: .45rem; font-size: .8rem; color: var(--fb-muted);
        }
        .fb-content-video {
            border-radius: 10px; overflow: hidden; background: #111827;
        }
        .fb-content-video iframe, .fb-content-video video {
            width: 100%; height: 100%; border: 0; display: block;
        }
        .fb-content-video-placeholder {
            display: flex; align-items: center; justify-content: center;
            min-height: 10rem; color: #9ca3af; font-size: .85rem;
        }
        .fb-content-footer.muted { font-size: .8rem; color: var(--fb-muted); }
        .fb-content-btn { display: inline-block; padding: .5rem .9rem; border-radius: 8px; background: var(--fb-primary); color: #111; font-weight: 600; text-decoration: none; }
        .fb-content-social { display: flex; flex-wrap: wrap; gap: .5rem; }
        .fb-content-btn-group { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .fb-content-btn-card { border: 1px solid var(--fb-border); border-radius: 10px; padding: .75rem; }
        .fb-content-section { margin: 0; }
        .fb-grid > .fb-content-section:not(:first-child) {
            margin-top: 0.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--fb-border);
        }
        .fb-section-container { width: 100%; }
        .fb-section-header { margin-bottom: .75rem; }
        .fb-section-title { margin: 0; font-size: 1.05rem; font-weight: 700; letter-spacing: -.01em; }
        .fb-section-divider { border: 0; border-top: 1px solid var(--fb-border); margin: 0 0 1rem; }
        .fb-section-body { width: 100%; }
        .fb-thankyou-card { background: var(--fb-card); border: 1px solid var(--fb-border); border-radius: var(--fb-radius); overflow: hidden; text-align: center; }
        .fb-thankyou-header { background: #ecfdf5; padding: 1.5rem 1rem 1rem; }
        .fb-thankyou-check { width: 48px; height: 48px; margin: 0 auto .75rem; border-radius: 999px; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 700; }
        .fb-thankyou-campaign .fb-thankyou-check { background: var(--fb-campaign-success-bg, #10b981); }
        .fb-thankyou-check-lg { width: 56px; height: 56px; font-size: 1.6rem; }
        .fb-thankyou-form-name { margin: 0; font-weight: 600; color: var(--fb-muted); }
        .fb-thankyou-body { padding: 1.25rem 1rem 1.5rem; }
        .fb-thankyou-title { margin: 0 0 .5rem; font-size: 1.35rem; }
        .fb-thankyou-message { margin: 0 0 .75rem; color: var(--fb-muted); }
        .fb-thankyou-time { margin: 0 0 1rem; font-size: .85rem; color: var(--fb-muted); }
        .fb-thankyou-submit-another { border: 0; border-radius: 10px; padding: .65rem 1rem; font: inherit; font-weight: 600; cursor: pointer; background: var(--fb-primary); color: #111; }
        .fb-thankyou-submit-preview { display: inline-block; opacity: .85; }
        .fb-thankyou-redirect { margin-top: .75rem; font-size: .9rem; }
        .fb-thankyou-campaign { position: relative; border: 1px solid var(--fb-border); border-radius: var(--fb-radius); padding: 2rem 1rem; text-align: center; overflow: hidden; background: var(--fb-campaign-page-bg, #f8fafc); animation: fb-thank-fade-in .45s ease; }
        .fb-thankyou-campaign-accents span { position: absolute; width: 180px; height: 180px; transform: rotate(45deg); opacity: .22; }
        .fb-thankyou-campaign-accents span:first-child { top: -70px; left: -50px; clip-path: polygon(0 0, 100% 0, 0 100%); }
        .fb-thankyou-campaign-accents span:last-child { bottom: -80px; right: -60px; clip-path: polygon(100% 0, 100% 100%, 0 100%); }
        .fb-thankyou-campaign-inner { position: relative; z-index: 1; max-width: 52rem; margin-inline: auto; }
        .fb-thankyou-campaign-copy { margin-bottom: 1rem; }
        .fb-thankyou-campaign-cards { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); margin: 1.25rem 0; text-align: left; }
        .fb-thankyou-campaign-card { background: #fff; border: 1px solid var(--fb-border); border-radius: 12px; padding: 1rem; box-shadow: 0 8px 24px rgb(15 23 42 / 0.06); }
        .fb-thankyou-campaign-card-title { margin: 0 0 .75rem; font-size: 1rem; }
        .fb-thankyou-campaign-social { display: flex; flex-wrap: wrap; gap: .5rem; }
        .fb-thankyou-campaign-social-link { display: inline-flex; align-items: center; justify-content: center; min-width: 2.25rem; height: 2.25rem; padding: 0 .55rem; border-radius: 999px; font-size: .72rem; font-weight: 700; color: #fff; text-decoration: none; }
        .fb-thankyou-campaign-social-link.is-facebook { background: #1877f2; }
        .fb-thankyou-campaign-social-link.is-linkedin { background: #0a66c2; }
        .fb-thankyou-campaign-social-link.is-pinterest { background: #e60023; }
        .fb-thankyou-campaign-social-link.is-twitter { background: #1d9bf0; }
        .fb-thankyou-campaign-social-link.is-instagram { background: #d62976; }
        .fb-thankyou-campaign-social-link.is-youtube { background: #ff0000; }
        .fb-thankyou-campaign-social-link.is-tiktok { background: #111827; }
        .fb-thankyou-campaign-btn { display: inline-block; margin-top: .25rem; padding: .55rem 1rem; border-radius: 8px; background: var(--fb-campaign-btn-bg, #10b981); color: #fff; font-weight: 600; text-decoration: none; }
        .fb-thankyou-campaign-btn-preview { opacity: .9; }
        .fb-thankyou-review { min-height: 24rem; padding: 2rem 1rem; background: var(--fb-review-page-bg, #3b82f6); border-radius: var(--fb-radius); animation: fb-thank-fade-in .45s ease; }
        .fb-thankyou-review-shell { display: flex; align-items: center; justify-content: center; min-height: 20rem; }
        .fb-thankyou-review-card { position: relative; width: min(100%, 24rem); background: #fff; border-radius: 16px; box-shadow: 0 18px 40px rgb(15 23 42 / 0.18); overflow: visible; text-align: center; }
        .fb-thankyou-review-header-band { height: 6.5rem; background: var(--fb-review-header-bg, #a5b4fc); border-radius: 16px 16px 0 0; }
        .fb-thankyou-review-hero { position: relative; margin-top: -2.6rem; margin-bottom: .5rem; z-index: 1; }
        .fb-thankyou-review-hero-emoji { display: inline-flex; align-items: center; justify-content: center; width: 5rem; height: 5rem; border-radius: 999px; background: #fff; font-size: 2rem; box-shadow: 0 10px 24px rgb(15 23 42 / 0.12); }
        .fb-thankyou-review-hero-img { width: 5rem; height: 5rem; object-fit: cover; border-radius: 999px; box-shadow: 0 10px 24px rgb(15 23 42 / 0.12); }
        .fb-thankyou-review-body { padding: 0 1.25rem 1.5rem; }
        .fb-thankyou-review-cta { display: inline-block; margin-top: .5rem; padding: .7rem 1.4rem; border-radius: 999px; background: var(--fb-review-cta-bg, #2563eb); color: #fff; font-size: .78rem; font-weight: 700; letter-spacing: .04em; text-decoration: none; text-transform: uppercase; }
        .fb-thankyou-review-cta-preview { opacity: .95; }
        .fb-thankyou-review-submit-another { margin-top: .75rem; background: transparent; color: var(--fb-muted); border: 1px solid var(--fb-border); }
        @keyframes fb-thank-fade-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 640px) {
            .fb-thankyou-campaign-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="fb-wrap">
        {{ $slot }}
    </main>
    @filamentScripts(withCore: true)
    <script src="{{ \Spiggle\FormBuilder\Support\PublicFormMaskAssets::url() }}" defer></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('fb-focus-field', (event) => {
                const id = event.id ?? event[0]?.id;
                requestAnimationFrame(() => {
                    const el = document.getElementById(id);
                    if (! el) return;
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (typeof el.focus === 'function') {
                        el.focus({ preventScroll: true });
                    }
                });
            });
        });
    </script>
</body>
</html>
