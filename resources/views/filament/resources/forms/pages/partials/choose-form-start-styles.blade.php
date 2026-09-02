<style>
    .cfs-wizard {
        --cfs-radius: 16px;
        --cfs-radius-sm: 12px;
        --cfs-border: color-mix(in srgb, currentColor 12%, transparent);
        --cfs-muted: color-mix(in srgb, currentColor 55%, transparent);
        --cfs-surface: color-mix(in srgb, currentColor 3%, transparent);
        --cfs-accent: #2563eb;
        --cfs-accent-ring: rgba(37, 99, 235, 0.28);
        --cfs-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 8px 24px rgba(0, 0, 0, 0.06);

        max-width: 640px;
        margin: 0 auto;
        padding: 1.75rem;
        border-radius: var(--cfs-radius);
        background: var(--cfs-surface);
        border: 1px solid var(--cfs-border);
        box-shadow: var(--cfs-shadow);
    }

    .cfs-wizard--wide {
        max-width: 820px;
    }

    .cfs-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .cfs-header-text {
        flex: 1;
        min-width: 0;
    }

    .cfs-title {
        margin: 0 0 0.35rem;
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .cfs-subtitle {
        margin: 0;
        font-size: 0.9rem;
        color: var(--cfs-muted);
        line-height: 1.45;
    }

    .cfs-step {
        flex-shrink: 0;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--cfs-muted);
        padding-top: 0.2rem;
    }

    .cfs-start-grid {
        display: grid;
        gap: 0.875rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .cfs-start-card {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        align-items: flex-start;
        padding: 1.25rem;
        border: 1px solid var(--cfs-border);
        border-radius: var(--cfs-radius-sm);
        background: #fff;
        cursor: pointer;
        text-align: left;
        font: inherit;
        color: inherit;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .dark .cfs-start-card {
        background: color-mix(in srgb, currentColor 5%, transparent);
    }

    .cfs-start-card:hover {
        border-color: var(--cfs-accent);
        box-shadow: 0 0 0 3px var(--cfs-accent-ring);
        transform: translateY(-1px);
    }

    .cfs-start-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 10px;
        background: color-mix(in srgb, var(--cfs-accent) 12%, transparent);
        color: var(--cfs-accent);
        font-size: 1.25rem;
        line-height: 1;
    }

    .cfs-start-card strong {
        font-size: 0.95rem;
    }

    .cfs-start-card span {
        font-size: 0.82rem;
        color: var(--cfs-muted);
        line-height: 1.4;
    }

    .cfs-category-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .cfs-category-card {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 1rem;
        border: 1px solid var(--cfs-border);
        border-radius: var(--cfs-radius-sm);
        background: #fff;
        cursor: pointer;
        text-align: left;
        font: inherit;
        color: inherit;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .dark .cfs-category-card {
        background: color-mix(in srgb, currentColor 5%, transparent);
    }

    .cfs-category-card:hover {
        border-color: var(--cfs-accent);
        box-shadow: 0 0 0 3px var(--cfs-accent-ring);
        transform: translateY(-1px);
    }

    .cfs-category-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 8px;
        background: color-mix(in srgb, var(--cfs-accent) 10%, transparent);
        color: var(--cfs-accent);
    }

    .cfs-category-icon svg {
        width: 18px;
        height: 18px;
    }

    .cfs-category-label {
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .cfs-category-count {
        font-size: 0.72rem;
        font-weight: 500;
        color: var(--cfs-muted);
        padding: 0.1rem 0.45rem;
        border-radius: 999px;
        background: color-mix(in srgb, currentColor 7%, transparent);
    }

    .cfs-search {
        width: 100%;
        margin-bottom: 1rem;
        padding: 0.6rem 0.85rem;
        border: 1px solid var(--cfs-border);
        border-radius: 10px;
        font: inherit;
        font-size: 0.875rem;
        background: #fff;
        color: inherit;
    }

    .dark .cfs-search {
        background: color-mix(in srgb, currentColor 5%, transparent);
    }

    .cfs-search:focus {
        outline: none;
        border-color: var(--cfs-accent);
        box-shadow: 0 0 0 3px var(--cfs-accent-ring);
    }

    .cfs-template-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-height: min(52vh, 480px);
        overflow-y: auto;
        padding-right: 0.15rem;
        margin-bottom: 1.25rem;
        scrollbar-width: thin;
    }

    .cfs-template-card {
        display: flex;
        align-items: stretch;
        gap: 1rem;
        width: 100%;
        padding: 0.875rem;
        border: 1.5px solid var(--cfs-border);
        border-radius: var(--cfs-radius-sm);
        background: #fff;
        cursor: pointer;
        text-align: left;
        font: inherit;
        color: inherit;
        position: relative;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .dark .cfs-template-card {
        background: color-mix(in srgb, currentColor 5%, transparent);
    }

    .cfs-template-card:hover {
        border-color: color-mix(in srgb, var(--cfs-accent) 50%, transparent);
    }

    .cfs-template-card.is-selected {
        border-color: var(--cfs-accent);
        box-shadow: 0 0 0 3px var(--cfs-accent-ring), 0 4px 16px rgba(37, 99, 235, 0.12);
    }

    .cfs-preview {
        flex-shrink: 0;
        width: 120px;
        border-radius: 8px;
        overflow: hidden;
    }

    .cfs-layout-svg {
        display: block;
        width: 120px;
        height: 80px;
    }

    .cfs-template-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.3rem;
        min-width: 0;
        padding-right: 2.5rem;
    }

    .cfs-template-info strong {
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .cfs-template-info span {
        font-size: 0.82rem;
        color: var(--cfs-muted);
        line-height: 1.4;
    }

    .cfs-pro-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: #fef3c7;
        color: #92400e;
    }

    .cfs-empty {
        padding: 2rem 1rem;
        text-align: center;
        font-size: 0.875rem;
        color: var(--cfs-muted);
    }

    .cfs-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.625rem;
        padding-top: 0.25rem;
    }

    .cfs-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font: inherit;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: opacity 0.15s, background 0.15s;
    }

    .cfs-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .cfs-btn--ghost {
        background: color-mix(in srgb, currentColor 8%, transparent);
        color: inherit;
    }

    .cfs-btn--ghost:hover:not(:disabled) {
        background: color-mix(in srgb, currentColor 12%, transparent);
    }

    .cfs-btn--primary {
        background: #111827;
        color: #fff;
    }

    .dark .cfs-btn--primary {
        background: #f9fafb;
        color: #111827;
    }

    .cfs-btn--primary:hover:not(:disabled) {
        opacity: 0.88;
    }

    @media (max-width: 640px) {
        .cfs-wizard {
            padding: 1.25rem;
            border-radius: 12px;
        }

        .cfs-start-grid {
            grid-template-columns: 1fr;
        }

        .cfs-category-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cfs-preview {
            width: 96px;
        }

        .cfs-layout-svg {
            width: 96px;
            height: 64px;
        }
    }

    @media (max-width: 420px) {
        .cfs-category-grid {
            grid-template-columns: 1fr;
        }

        .cfs-template-card {
            flex-direction: column;
        }

        .cfs-preview {
            width: 100%;
        }

        .cfs-layout-svg {
            width: 100%;
            height: auto;
        }
    }
</style>
