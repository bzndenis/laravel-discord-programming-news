# Discord Bot - Production Deployment Script (PowerShell)
# Usage: .\deploy.ps1

Write-Host "🚀 Starting Discord Bot Deployment..." -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: artisan file not found. Please run this script from the project root." -ForegroundColor Red
    exit 1
}

Write-Host "Step 1: Clearing caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
Write-Host "✅ Caches cleared" -ForegroundColor Green
Write-Host ""

Write-Host "Step 2: Rebuilding caches..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
Write-Host "✅ Caches rebuilt" -ForegroundColor Green
Write-Host ""

Write-Host "Step 3: Verifying configuration..." -ForegroundColor Yellow
Write-Host "Discord App ID:"
php artisan tinker --execute="echo config('discord.app_id') ?? 'Not configured';"
Write-Host ""

Write-Host "Step 4: Verifying routes..." -ForegroundColor Yellow
php artisan route:list --path=discord
Write-Host ""

Write-Host "Step 5: Creating upload checklist..." -ForegroundColor Yellow
$files = @(
    "routes\web.php",
    "app\Http\Controllers\DiscordInteractionController.php",
    "app\Services\DiscordInteractionService.php",
    "config\discord.php",
    "app\Jobs\ProcessFeatureUpdate.php",
    "app\Console\Commands\RegisterDiscordCommands.php"
)

Write-Host "`nFiles ready for upload:" -ForegroundColor Cyan
foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $file (NOT FOUND)" -ForegroundColor Red
    }
}
Write-Host ""

Write-Host "🎉 Local checks complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Cyan
Write-Host "1. Upload the files listed above to your production server"
Write-Host "2. SSH to server and run: bash deploy.sh"
Write-Host "3. Set Interactions Endpoint URL in Discord Developer Portal"
Write-Host "4. Test slash commands in Discord"
Write-Host ""
