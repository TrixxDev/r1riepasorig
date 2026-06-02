# Enables ODBC extensions in local PHP 7.4 for Accrual MSSQL access.
# Run in PowerShell: .\scripts\enable-accrual-php.ps1

$phpIni = "E:\dev-tools\php\7.4\php.ini"
if (-not (Test-Path $phpIni)) {
    Write-Error "php.ini not found: $phpIni"
    exit 1
}

$content = Get-Content $phpIni -Raw
$content = $content -replace ';extension=odbc', 'extension=odbc'
$content = $content -replace ';extension=pdo_odbc', 'extension=pdo_odbc'
Set-Content $phpIni $content -NoNewline

Write-Host "Enabled extension=odbc and extension=pdo_odbc in $phpIni"
& "E:\dev-tools\php\7.4\php.exe" -r "print_r(PDO::getAvailableDrivers());"
