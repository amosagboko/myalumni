# Admin UI Kit

Portable **admin design system** for Laravel + Bootstrap (myalumni).

Two pattern families, one visual language:

| Prefix | Use for |
|--------|---------|
| **`ads-*`** | Surfaces — dashboards, stats, sections, modals, quick actions |
| **`adt-*`** | Tables — lists, filters toolbar, pagination, row actions |

Shared tokens: `--adt-primary`, `--adt-border`, etc. in `ui-tokens.blade.php`.

## Install

From target project root (PowerShell):

```powershell
.\admin-ui-kit\install-to-laravel.ps1
```

Or bash:

```bash
bash admin-ui-kit/install-to-laravel.sh
```

Requires `@stack('styles')` in your layout `<head>`.

## Usage

**Dashboard / overview (no table):**

```blade
<x-admin.surface-styles />

<div class="main-content admin-surface">
    <div class="ads-page-header">...</div>
    <div class="ads-section">
        <div class="ads-stats">...</div>
    </div>
</div>
```

**List page with stats + table:**

```blade
<x-admin.surface-styles />
<x-admin.data-table-styles />

<div class="main-content admin-data-table">
    <div class="ads-page-header">...</div>
    <div class="ads-stats">...</div>
    <div class="adt-panel">...</div>
</div>
```

## Tell Cursor

```text
Use the admin UI kit:
- Surfaces: .cursor/rules/admin-surfaces.mdc
- Tables: .cursor/rules/admin-data-table.mdc
- Reference dashboard: resources/views/livewire/admin/dashboard.blade.php
- Reference table: resources/views/livewire/admin/manage-users.blade.php
```

See `PROMPT-FOR-CURSOR.md` for a fill-in template.

## Files installed

| Component | Path |
|-----------|------|
| Tokens | `resources/views/components/admin/ui-tokens.blade.php` |
| Surfaces | `resources/views/components/admin/surface-styles.blade.php` |
| Tables | `resources/views/components/admin/data-table-styles.blade.php` |
| Surface skeleton | `resources/views/templates/admin-surface-page-skeleton.blade.php` |
| Table skeleton | `resources/views/templates/admin-data-table-page-skeleton.blade.php` |
| Cursor rules | `.cursor/rules/admin-surfaces.mdc`, `admin-data-table.mdc` |

## Customize brand

Edit `--adt-primary` in `ui-tokens.blade.php` (default `#132977`).

## Legacy

`admin-data-table-kit/` remains for table-only copies; prefer this kit for new projects.
