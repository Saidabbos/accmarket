#################################################################
# AccMarket - Push va Remote Deploy Script (Windows PowerShell)
# Local o'zgarishlarni push qilib, serverda deploy qiladi
#################################################################

# Konfiguratsiya - O'ZGARTIRING!
$SERVER_USER = "root"
$SERVER_IP = "YOUR_SERVER_IP"  # Masalan: 164.92.xxx.xxx
$APP_DIR = "/var/www/accssio"
$BRANCH = "master"

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   AccMarket Push & Deploy Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Git status tekshirish
Write-Host "[INFO] Git status tekshirilmoqda..." -ForegroundColor Blue

$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host ""
    git status --short
    Write-Host ""

    # Commit message so'rash
    $COMMIT_MSG = Read-Host "Commit message kiriting"

    if ([string]::IsNullOrEmpty($COMMIT_MSG)) {
        $COMMIT_MSG = "Update: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    }

    # Git add va commit
    Write-Host "[INFO] O'zgarishlar commit qilinmoqda..." -ForegroundColor Blue
    git add .
    git commit -m $COMMIT_MSG
    Write-Host "[SUCCESS] Commit yaratildi: $COMMIT_MSG" -ForegroundColor Green
} else {
    Write-Host "[INFO] O'zgarishlar yo'q, faqat push qilinadi" -ForegroundColor Blue
}

# 2. Git push
Write-Host "[INFO] GitHub'ga push qilinmoqda..." -ForegroundColor Blue
git push origin $BRANCH
Write-Host "[SUCCESS] Push muvaffaqiyatli" -ForegroundColor Green

# 3. Serverda deploy
Write-Host "[INFO] Serverda deploy boshlanmoqda..." -ForegroundColor Blue
Write-Host ""

$deployScript = @'
set -e

APP_DIR="/var/www/accssio"
PHP_VERSION="8.2"

echo "🚀 Deploy boshlandi..."

cd $APP_DIR

# Maintenance mode
echo "⏸️  Maintenance mode..."
php artisan down --retry=60 --refresh=5 || true

# Git pull
echo "📥 Git pull..."
git fetch origin master
git reset --hard origin/master

# Composer
echo "📦 Composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# NPM
echo "🔨 NPM build..."
npm ci --silent
npm run build

# Migrations
echo "🗄️  Migrations..."
php artisan migrate --force

# Cache
echo "🧹 Cache clear & rebuild..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Queue restart
echo "🔄 Queue restart..."
php artisan queue:restart

# Permissions
echo "🔐 Permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# PHP-FPM reload
echo "🔄 PHP-FPM reload..."
sudo systemctl reload php8.2-fpm

# Maintenance off
echo "✅ Going live..."
php artisan up

echo ""
echo "=========================================="
echo "   Deploy muvaffaqiyatli yakunlandi!"
echo "=========================================="
'@

# SSH orqali deploy
ssh "${SERVER_USER}@${SERVER_IP}" $deployScript

Write-Host ""
Write-Host "[SUCCESS] Deploy yakunlandi!" -ForegroundColor Green
Write-Host ""
Write-Host "Sayt: https://accssio.com" -ForegroundColor Cyan
Write-Host ""
