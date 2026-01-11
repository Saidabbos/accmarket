#!/bin/bash

#################################################################
# AccMarket - Production Deploy Script (Docker'siz)
# Digital Ocean Droplet uchun
#################################################################

set -e

# Ranglar
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Konfiguratsiya
APP_DIR="/var/www/accssio"
BRANCH="master"
PHP_VERSION="8.2"

# Log funksiya
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

# Xato bo'lganda chiqish
handle_error() {
    log_error "Xato yuz berdi: $1"
    log_warning "Maintenance mode'ni o'chirish..."
    cd $APP_DIR && php artisan up 2>/dev/null || true
    exit 1
}

trap 'handle_error "Script to'\''xtatildi"' ERR

echo ""
echo "=========================================="
echo "   AccMarket Production Deploy Script"
echo "=========================================="
echo ""

# Papka mavjudligini tekshirish
if [ ! -d "$APP_DIR" ]; then
    log_error "Loyiha papkasi topilmadi: $APP_DIR"
    exit 1
fi

cd $APP_DIR

# 1. Maintenance mode
log_info "Maintenance mode yoqilmoqda..."
php artisan down --retry=60 --refresh=5 || true
log_success "Sayt maintenance rejimida"

# 2. Git pull
log_info "Git'dan yangilanishlar yuklanmoqda..."
git fetch origin $BRANCH
git reset --hard origin/$BRANCH
log_success "Kod yangilandi"

# 3. Composer dependencies
log_info "Composer dependencies o'rnatilmoqda..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
log_success "Composer dependencies o'rnatildi"

# 4. NPM dependencies va build
log_info "NPM dependencies o'rnatilmoqda..."
npm ci --silent
log_success "NPM dependencies o'rnatildi"

log_info "Frontend build qilinmoqda..."
npm run build
log_success "Frontend build tayyor"

# 5. Database migrations
log_info "Migratsiyalar ishga tushirilmoqda..."
php artisan migrate --force
log_success "Migratsiyalar bajarildi"

# 6. Cache tozalash
log_info "Keshlar tozalanmoqda..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
log_success "Keshlar tozalandi"

# 7. Cache yaratish
log_info "Yangi keshlar yaratilmoqda..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
log_success "Keshlar yaratildi"

# 8. Storage link
log_info "Storage link tekshirilmoqda..."
php artisan storage:link 2>/dev/null || true
log_success "Storage link tayyor"

# 9. Queue restart
log_info "Queue worker qayta ishga tushirilmoqda..."
php artisan queue:restart
log_success "Queue worker qayta ishga tushdi"

# 10. Ruxsatlarni sozlash
log_info "Fayl ruxsatlari sozlanmoqda..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
log_success "Ruxsatlar sozlandi"

# 11. PHP-FPM qayta ishga tushirish
log_info "PHP-FPM qayta ishga tushirilmoqda..."
sudo systemctl reload php${PHP_VERSION}-fpm
log_success "PHP-FPM qayta ishga tushdi"

# 12. Supervisor qayta yuklash (agar mavjud bo'lsa)
if command -v supervisorctl &> /dev/null; then
    log_info "Supervisor qayta yuklanmoqda..."
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl restart all
    log_success "Supervisor qayta yuklandi"
fi

# 13. Maintenance mode o'chirish
log_info "Maintenance mode o'chirilmoqda..."
php artisan up
log_success "Sayt ishga tushdi!"

echo ""
echo "=========================================="
echo -e "${GREEN}   Deploy muvaffaqiyatli yakunlandi!${NC}"
echo "=========================================="
echo ""
echo "Sayt: https://accssio.com"
echo "Vaqt: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
