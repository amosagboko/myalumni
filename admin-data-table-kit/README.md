# Admin Data Table Kit

Portable table UI from **myalumni** (Manage Users). Copy this folder into any Laravel + Bootstrap admin project.

## Quick install

From your **target project** root (PowerShell):

```powershell
.\admin-data-table-kit\install-to-laravel.ps1
```

Or bash:

```bash
bash admin-data-table-kit/install-to-laravel.sh
```

Manual copy:

| Kit file | Copy to |
|----------|---------|
| `laravel/.cursor/rules/admin-data-table.mdc` | `.cursor/rules/admin-data-table.mdc` |
| `laravel/resources/views/components/admin/data-table-styles.blade.php` | `resources/views/components/admin/data-table-styles.blade.php` |
| `laravel/resources/views/templates/admin-data-table-page-skeleton.blade.php` | optional starter (any path) |

Then ensure your layout has `@stack('styles')` before `</head>`.

## Requirements

- Laravel Blade views
- Bootstrap 5 (`btn`, `form-control`, `form-select`, `pagination::bootstrap-5`)
- Feather icons optional (`data-feather`, call `feather.replace()` after render)

## Use on a page

```blade
<x-admin.data-table-styles />

<div class="main-content admin-data-table">
    {{-- See templates/admin-data-table-page-skeleton.blade.php --}}
</div>
```

## Tell Cursor to use it

After installing, say:

```text
Use the admin data table kit:
- Rule: .cursor/rules/admin-data-table.mdc
- Styles: resources/views/components/admin/data-table-styles.blade.php
- Skeleton: resources/views/templates/admin-data-table-page-skeleton.blade.php
- Apply to: [your page path]
- Columns: [list]
```

Or shorter:

```text
Format this table with our admin-data-table kit (adt-* classes).
```

## Customize brand color

Edit `--adt-primary` in `data-table-styles.blade.php` (default `#132977`).

## Origin

Exported from `myalumni` — reference implementation: `resources/views/livewire/admin/manage-users.blade.php`.
