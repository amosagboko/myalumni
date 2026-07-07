$ErrorActionPreference = "Stop"
$KitRoot = $PSScriptRoot
$ProjectRoot = Get-Location

$Copies = @(
    @{ Source = "laravel\.cursor\rules\admin-surfaces.mdc"; Dest = ".cursor\rules\admin-surfaces.mdc" },
    @{ Source = "laravel\.cursor\rules\admin-data-table.mdc"; Dest = ".cursor\rules\admin-data-table.mdc" },
    @{ Source = "laravel\resources\views\components\admin\ui-tokens.blade.php"; Dest = "resources\views\components\admin\ui-tokens.blade.php" },
    @{ Source = "laravel\resources\views\components\admin\surface-styles.blade.php"; Dest = "resources\views\components\admin\surface-styles.blade.php" },
    @{ Source = "laravel\resources\views\components\admin\data-table-styles.blade.php"; Dest = "resources\views\components\admin\data-table-styles.blade.php" },
    @{ Source = "laravel\resources\views\templates\admin-surface-page-skeleton.blade.php"; Dest = "resources\views\templates\admin-surface-page-skeleton.blade.php" },
    @{ Source = "laravel\resources\views\templates\admin-data-table-page-skeleton.blade.php"; Dest = "resources\views\templates\admin-data-table-page-skeleton.blade.php" }
)

foreach ($item in $Copies) {
    $src = Join-Path $KitRoot $item.Source
    $dest = Join-Path $ProjectRoot $item.Dest
    $destDir = Split-Path $dest -Parent
    if (-not (Test-Path $destDir)) { New-Item -ItemType Directory -Path $destDir -Force | Out-Null }
    Copy-Item -Path $src -Destination $dest -Force
    Write-Host "Installed: $($item.Dest)"
}

Write-Host ""
Write-Host "Done. See admin-ui-kit\README.md"
