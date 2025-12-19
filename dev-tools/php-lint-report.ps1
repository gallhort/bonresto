# Run PHP syntax check on modified and important files
# Usage: Open PowerShell in project root and run: .\dev-tools\php-lint-report.ps1

$php = "php"
# If php is not in PATH, user should provide the full path to php.exe
if (-not (Get-Command $php -ErrorAction SilentlyContinue)) {
    Write-Host "`nPHP executable not found in PATH. Please install PHP or provide full path in the script.`n" -ForegroundColor Yellow
    exit 1
}

# Files to lint: modified files and some common entry points
$files = @(
    'connect.php',
    'classes/connect.php',
    'getdata.php',
    'get_map_data.php',
    'api_restaurants.php',
    'getresto.php',
    'gettype.php',
    'searchCity.php',
    'result.php',
    'debug.php',
    'change photo name.php'
)

$errors = @()
foreach ($f in $files) {
    if (-not (Test-Path $f)) { continue }
    Write-Host "Checking $f ..." -NoNewline
    $r = & $php -l $f 2>&1
    if ($LASTEXITCODE -ne 0) {
        $errors += @{file=$f; msg=$r}
        Write-Host " FAILED" -ForegroundColor Red
    } else {
        Write-Host " OK" -ForegroundColor Green
    }
}

if ($errors.Count -gt 0) {
    Write-Host "`nSummary: Found $($errors.Count) PHP syntax errors:" -ForegroundColor Red
    foreach ($e in $errors) {
        Write-Host "--- $($e.file) ---" -ForegroundColor Yellow
        Write-Host $e.msg
    }
    exit 2
}

Write-Host "`nAll checked files passed php -l" -ForegroundColor Green
exit 0
