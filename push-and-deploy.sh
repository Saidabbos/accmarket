#!/bin/bash

#################################################################
# AccMarket - Push va Remote Deploy Script
# Local o'zgarishlarni push qilib, serverda deploy qiladi
#################################################################

set -e

# Ranglar
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Konfiguratsiya - O'ZGARTIRING!
SERVER_USER="root"
SERVER_IP="178.128.205.23"  # Masalan: 164.92.xxx.xxx
APP_DIR="/var/www/accssio"
BRANCH="master"

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo ""
echo "=========================================="
echo "   AccMarket Push & Deploy Script"
echo "=========================================="
echo ""

# 1. Git status tekshirish
log_info "Git status tekshirilmoqda..."
if [[ -n $(git status --porcelain) ]]; then
    echo ""
    git status --short
    echo ""

    # Commit message so'rash
    read -p "Commit message kiriting: " COMMIT_MSG

    if [ -z "$COMMIT_MSG" ]; then
        COMMIT_MSG="Update: $(date '+%Y-%m-%d %H:%M:%S')"
    fi

    # Git add va commit
    log_info "O'zgarishlar commit qilinmoqda..."
    git add .
    git commit -m "$COMMIT_MSG"
    log_success "Commit yaratildi: $COMMIT_MSG"
else
    log_info "O'zgarishlar yo'q, faqat push qilinadi"
fi

# 2. Git push
log_info "GitHub'ga push qilinmoqda..."
git push origin $BRANCH
log_success "Push muvaffaqiyatli"

# 3. Serverda deploy
log_info "Serverda deploy boshlanmoqda..."
echo ""

ssh ${SERVER_USER}@${SERVER_IP} << 'ENDSSH'
set -e

APP_DIR="/var/www/accssio"
PHP_VERSION="8.2"

echo "🚀 Deploy boshlandi..."

cd $APP_DIR

# Maintenance mode
echo "⏸️  Maintenance mode..."
php artisan down --retry=60 --refresh=5 || true

# Git - local o'zgarishlarni stash qilish va pull
echo "📥 Git pull..."
git stash --include-untracked 2>/dev/null || true
git fetch origin master
git pull origin master --no-edit

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

# Stash qilingan o'zgarishlarni qaytarish
echo "📂 Stash qilingan o'zgarishlar qaytarilmoqda..."
git stash pop 2>/dev/null || echo "   Stash bo'sh yoki conflict bor"

# Maintenance off
echo "✅ Going live..."
php artisan up

echo ""
echo "=========================================="
echo "   Deploy muvaffaqiyatli yakunlandi!"
echo "=========================================="
ENDSSH

echo ""
log_success "Deploy yakunlandi!"
echo ""
echo "Sayt: https://accssio.com"
echo ""
