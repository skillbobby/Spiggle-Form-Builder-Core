<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @livewireStyles
    <style>
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
            background: var(--fb-card);
            border: 1px solid var(--fb-border);
            border-radius: var(--fb-radius);
            box-shadow: 0 10px 40px rgba(15, 23, 42, .06);
            padding: 1.25rem;
        }
        @media (min-width: 768px) { .fb-card { padding: 2rem; } }
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
        .fb-input, .fb-select, .fb-textarea, .fb-file {
            width: 100%;
            border: 1px solid var(--fb-border);
            border-radius: 10px;
            padding: .65rem .75rem;
            font: inherit;
            background: #fff;
        }
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
        }
        .fb-pill[aria-selected="true"] { background: #fffbeb; border-color: var(--fb-primary); font-weight: 600; }
        .fb-success { text-align: center; padding: 2rem 1rem; }
        .fb-radio, .fb-check { display: flex; flex-direction: column; gap: .4rem; }
        .fb-radio label, .fb-check label { font-weight: 400; display: flex; gap: .5rem; align-items: center; }
        .fb-hidden { display: none; }
    </style>
</head>
<body>
    <main class="fb-wrap">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
