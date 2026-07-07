# Admin UI kit — prompt for Cursor

```text
Use the admin UI kit in this project.

Surfaces (ads-*): .cursor/rules/admin-surfaces.mdc
Tables (adt-*): .cursor/rules/admin-data-table.mdc

Components:
- resources/views/components/admin/surface-styles.blade.php
- resources/views/components/admin/data-table-styles.blade.php

Skeletons:
- resources/views/templates/admin-surface-page-skeleton.blade.php
- resources/views/templates/admin-data-table-page-skeleton.blade.php

References:
- Dashboard: resources/views/livewire/admin/dashboard.blade.php
- Table page: resources/views/livewire/admin/manage-users.blade.php

Target: [PATH OR ROUTE]
Page type: [dashboard | table | both]
Title: [TITLE]
Sections/stats: [describe KPIs]
Columns (if table): [list]
Actions: [describe]

Rules: neutral palette, no colored Bootstrap stat cards, ads-btn-primary with white text, ads-modal-* for dialogs (not Bootstrap Modal).
```
