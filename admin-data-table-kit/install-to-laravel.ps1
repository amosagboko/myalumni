# Install admin-data-table-kit into current Laravel project
$ErrorActionPreference = "Stop"
$KitRoot = $PSScriptRoot
$ProjectRoot = Get-Location

$Copies = @(
    @{
        Source = Join-Path $KitRoot "laravel\.cursor\rules\admin-data-table.mdc"
        Dest   = Join-Path $ProjectRoot ".cursor\rules\admin-data-table.mdc"
    },
    @{
        Source = Join-Path $KitRoot "laravel\resources\views\components\admin\data-table-styles.blade.php"
        Dest   = Join-Path $ProjectRoot "resources\views\components\admin\data-table-styles.blade.php"
    },
    @{
        Source = Join-Path $KitRoot "laravel\resources\views\templates\admin-data-table-page-skeleton.blade.php"
        Dest   = Join-Path $ProjectRoot "resources\views\templates\admin-data-table-page-skeleton.blade.php"
    }
)

foreach ($item in $Copies) {
    $destDir = Split-Path $item.Dest -Parent
    if (-not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }
    Copy-Item -Path $item.Source -Destination $item.Dest -Force
    Write-Host "Installed: $($item.Dest)"
}

Write-Host ""
Write-Host "Done. Add @stack('styles') to your layout if missing, then use <x-admin.data-table-styles /> on table pages."
Write-Host "See admin-data-table-kit\README.md and PROMPT-FOR-CURSOR.md"
