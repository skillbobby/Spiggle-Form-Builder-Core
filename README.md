# Spiggle Form Builder Core

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-5-FFAA00)](https://filamentphp.com)

**Public forms for Laravel + Filament — build once, publish anywhere.**

Create forms in the admin panel, share a public URL, and collect submissions without shipping a custom frontend for every intake form.

**[Watch the demo on YouTube →](https://youtu.be/HtSdjyPaJhw)**

| | |
|---|---|
| **Package** | `spiggle/form-builder-core` v1.1.3 |
| **License** | MIT (Community Edition) |
| **GitHub** | [skillbobby/Spiggle-Form-Builder-Core](https://github.com/skillbobby/Spiggle-Form-Builder-Core) |
| **Docs** | [Product site & guide](https://skillbobby.github.io/Spiggle-Form-Builder-Core/) |

---

## Why use Form Builder?

- **No-code form building** — drag sections and fields in Filament; publish when ready
- **Public renderer** — self-contained Blade + Livewire UI that works without Filament CSS on the guest side
- **Submission inbox** — filter, review, archive, and export responses
- **Validation & sanitization** — Laravel rules on submit; HTML stripped from plain text
- **Custom public paths** — `/forms/{path}` with conflict hashing
- **Dynamic Fields ready** — reuses the same field type catalog when Dynamic Fields is installed
- **Pro-ready** — `FeatureCatalog` / `ProUnlock` hooks let the licensed add-on unlock advanced layouts and exports

---

## Community Edition features

Included free in Core:

| | |
|---|---|
| Single-page layouts | Public form URL + Livewire renderer |
| Submission manager | Status filters, bulk status/archive/delete |
| CSV export | Native `fputcsv` |
| Form JSON export/import | Move definitions between environments |
| Seed / verify commands | Sample forms + health checks |
| Audit log table | Exports and bulk actions recorded |
| Label positions | Above, inline, below, inside |

---

## Pro features

Need multi-step flows, premium exports, or analytics? Upgrade to **Form Builder Pro** — a separate licensed add-on that installs alongside Core.

| Pro-only | |
|---|---|
| **Wizard / Tabs / Pages** | Stepped validation, client tabs, session drafts |
| **XLSX + PDF export** | SpreadsheetML Excel + printable PDF |
| **Analytics charts** | Submission volume and status widgets |
| **Form clone** | Duplicate a form in one click |
| **Import from Dynamic Fields** | Pull existing custom field definitions into a form |
| **Email notify hooks** | Store notify addresses for your `FormSubmitted` listeners |
| **License management** | Activate and manage your Pro license in Filament |

**[Get Pro license](https://kodesmart.lemonsqueezy.com/checkout/buy/c23b259f-7845-41c3-aa24-61e92c29dc72?enabled=2043888)**

Business / unlimited-site licensing: **[Get Business license](https://kodesmart.lemonsqueezy.com/checkout/buy/9a5c685c-afb5-4ef8-a61c-f4798cc8d1d2?enabled=2043889)**.

---

## Requirements

- PHP **8.3+**
- Laravel **13**
- Filament **^5**

---

## Installation

### 1. Require the package

```bash
composer require spiggle/form-builder-core
```

### 2. Publish config & migrations, then migrate

```bash
php artisan vendor:publish --tag=form-builder-config
php artisan vendor:publish --tag=form-builder-migrations
php artisan migrate
```

### 3. Register the Filament plugin

In your panel provider (e.g. `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Spiggle\FormBuilder\Filament\FormBuilderPlugin;

$panel->plugin(FormBuilderPlugin::make());
```

This adds **Forms** and **Submissions** under your configured navigation group.

### 4. Open a public form

Published forms are served at:

```text
/{FORM_BUILDER_ROUTE_PREFIX}/{base_path}
```

Default prefix: `forms`.

---

## Quick start

1. Sign in to your Filament panel.
2. Go to **Forms → Forms**.
3. Create a form, add fields, set **Published**.
4. Share the public URL from the form list or view page.

Seed sample forms:

```bash
php artisan form-builder:seed
```

---

## Artisan commands

| Command | Description |
|---|---|
| `form-builder:seed` | Seed sample forms and submissions |
| `form-builder:export` | Export form definitions to JSON |
| `form-builder:import {path}` | Import form definitions from JSON |
| `form-builder:verify` | Health-check tables, validation, and CSV export |

---

## Related

- **[Full documentation](https://skillbobby.github.io/Spiggle-Form-Builder-Core/guide/)** — architecture, workflows, configuration
- **[Demo video](https://youtu.be/HtSdjyPaJhw)** — Forms, public URL, builder, and submissions
- **[Live demo](https://skillbobby.com/larafill/public/admin/login)** — `demo@user.net` / `password`
- **[Spiggle Dynamic Fields Core](https://skillbobby.github.io/Spiggle-Dynamic-Fields-Core/)** — reusable field types for Eloquent models

---

## License

MIT — see [LICENSE](LICENSE). Community Edition is free and open source. Pro is a separate commercial add-on.
