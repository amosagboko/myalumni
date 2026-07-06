#!/usr/bin/env bash
set -euo pipefail

KIT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(pwd)"

install_file() {
  local src="$1"
  local dest="$2"
  mkdir -p "$(dirname "$dest")"
  cp "$src" "$dest"
  echo "Installed: $dest"
}

install_file "$KIT_ROOT/laravel/.cursor/rules/admin-data-table.mdc" \
  "$PROJECT_ROOT/.cursor/rules/admin-data-table.mdc"

install_file "$KIT_ROOT/laravel/resources/views/components/admin/data-table-styles.blade.php" \
  "$PROJECT_ROOT/resources/views/components/admin/data-table-styles.blade.php"

install_file "$KIT_ROOT/laravel/resources/views/templates/admin-data-table-page-skeleton.blade.php" \
  "$PROJECT_ROOT/resources/views/templates/admin-data-table-page-skeleton.blade.php"

echo ""
echo "Done. Add @stack('styles') to your layout if missing, then use <x-admin.data-table-styles /> on table pages."
echo "See admin-data-table-kit/README.md and PROMPT-FOR-CURSOR.md"
