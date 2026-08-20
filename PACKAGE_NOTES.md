# PACKAGE_NOTES — Architecture decisions

Spec source: *Package SRD [Form-Builder].txt*

## Naming

| Spec | Choice |
|---|---|
| Core package | Composer `spiggle/form-builder-core` in this directory |
| Pro package | Composer `spiggle/form-builder-pro` in `packages/spiggle-form-builder` |
| Core namespace | `Spiggle\FormBuilder` (stable public APIs) |
| Pro namespace | `Spiggle\FormBuilder\Pro` |
| Filament plugin | `FormBuilderPlugin` (`spiggle-form-builder`) |
| Tables | `form_builder_forms`, `form_builder_submissions`, `form_builder_audit_logs` |

## Core vs Pro feature split

| Feature | Core | Pro |
|---|---|---|
| Single-page layout | ✓ | ✓ |
| Wizard / Tabs / Pages | badge + upsell | ✓ (licensed) |
| Public renderer + submissions | ✓ | ✓ |
| CSV export | ✓ | ✓ |
| XLSX / PDF export | gated | ✓ |
| Analytics charts | gated | ✓ |
| Form clone | gated | ✓ |
| Import from Dynamic Fields | gated | ✓ |
| Email notify hooks (addresses) | gated | ✓ |
| Page drafts (session) | gated | ✓ |
| Form JSON export/import | ✓ | ✓ |
| Seed / verify | ✓ | ✓ |
| Lemon Squeezy licensing | — | ✓ |

Gating uses `FeatureCatalog::proUnlocked()` backed by Core `Contracts\ProUnlock`. Pro binds `LicenseManager` as `ProUnlock`.

## Two-package target

- Public GitHub: `skillbobby/Spiggle-Form-Builder-Core` (Pages from `/docs`)
- Private GitHub: `skillbobby/Spiggle-Form-Builder-Pro`
- Legacy private `Spiggle-Form-Builder` remains private (not made public)

## Filament version

Host app uses **Filament v5**. Nested `Repeater` builders provide SortableJS drag-and-drop without a custom Alpine board.

## Dynamic Fields composition

- `FieldCatalog` delegates to `Spiggle\DynamicFields\Support\FieldTypes` when present.
- Suggest `spiggle/dynamic-fields-core` (not hard-required).
- `Form::importCustomFields()` is Pro-gated.

## Licensing (Pro only)

Encrypted license file, server fingerprint, Filament license page, daily verify command, offline grace. Config keys under `form-builder.licensing.*` / `FORM_BUILDER_*` env vars. Checkout URL shared with Dynamic Fields product for now.
