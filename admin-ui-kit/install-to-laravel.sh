#!/usr/bin/env bash
set -euo pipefail
KIT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(pwd)"

install_file() {
  mkdir -p "$(dirname "$2")"
  cp "$1" "$2"
  echo "Installed: $2"
}

install_file "$KIT_ROOT/laravel/.cursor/rules/admin-surfaces.mdc" "$PROJECT_ROOT/.cursor/rules/admin-surfaces.mdc"
install_file "$KIT_ROOT/laravel/.cursor/rules/admin-data-table.mdc" "$PROJECT_ROOT/.cursor/rules/admin-data-table.mdc"
install_file "$KIT_ROOT/laravel/resources/views/components/admin/ui-tokens.blade.php" "$PROJECT_ROOT/resources/views/components/admin/ui-tokens.blade.php"
install_file "$KIT_ROOT/laravel/resources/views/components/admin/surface-styles.blade.php" "$PROJECT_ROOT/resources/views/components/admin/surface-styles.blade.php"
install_file "$KIT_ROOT/laravel/resources/views/components/admin/data-table-styles.blade.php" "$PROJECT_ROOT/resources/views/components/admin/data-table-styles.blade.php"
install_file "$KIT_ROOT/laravel/resources/views/templates/admin-surface-page-skeleton.blade.php" "$PROJECT_ROOT/resources/views/templates/admin-surface-page-skeleton.blade.php"
install_file "$KIT_ROOT/laravel/resources/views/templates/admin-data-table-page-skeleton.blade.php" "$PROJECT_ROOT/resources/views/templates/admin-data-table-page-skeleton.blade.php"

echo ""
echo "Done. See admin-ui-kit/README.md"
