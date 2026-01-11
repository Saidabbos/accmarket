#!/bin/bash

#################################################################
# AccMarket - Server Setup Script (Docker'siz)
# Digital Ocean Droplet - Ubuntu 22.04 LTS uchun
# Birinchi marta o'rnatish uchun
#################################################################

set -e

# Ranglar
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Konfiguratsiya - BU YERLARNI O'ZGARTIRING!
DOMAIN="accssio.com"
APP_DIR="/var/www/accssio"
DB_NAME="accssio"
DB_USER="accssio"
DB_PASS="KUCHLI_PAROL_KIRITING"  # O'ZGARTIRING!
GIT_REPO="https://github.com/Saidabbos/accmarket.git"
PHP_VERSION="8.2"

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
echo "   AccMarket Server Setup Script"
echo "   Ubuntu 22.04 LTS"
echo "=========================================="
echo ""

# Root tekshirish
if [ "$EUID" -ne 0 ]; then
    log_error "Bu skriptni root sifatida ishga tushiring: sudo bash server-setup.sh"
    exit 1
fi

# 1. Tizimni yangilash
log_info "Tizim yangilanmoqda..."
apt update && apt upgrade -y
log_success "Tizim yangilandi"

# 2. PHP o'rnatish
log_info "PHP ${PHP_VERSION} o'rnatilmoqda..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysql php${PHP_VERSION}-zip php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-curl php${PHP_VERSION}-xml php${PHP_VERSION}-bcmath php${PHP_VERSION}-intl \
    php${PHP_VERSION}-readline php${PHP_VERSION}-redis
log_success "PHP ${PHP_VERSION} o'rnatildi"

# 3. Nginx o'rnatish
log_info "Nginx o'rnatilmoqda..."
apt install -y nginx
systemctl enable nginx
systemctl start nginx
log_success "Nginx o'rnatildi"

# 4. MySQL o'rnatish
log_info "MySQL o'rnatilmoqda..."
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql
log_success "MySQL o'rnatildi"

# 5. Redis o'rnatish
log_info "Redis o'rnatilmoqda..."
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server
log_success "Redis o'rnatildi"

# 6. Composer o'rnatish
log_info "Composer o'rnatilmoqda..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
log_success "Composer o'rnatildi"

# 7. Node.js o'rnatish
log_info "Node.js o'rnatilmoqda..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
log_success "Node.js o'rnatildi"

# 8. Git o'rnatish
log_info "Git o'rnatilmoqda..."
apt install -y git
log_success "Git o'rnatildi"

# 9. Supervisor o'rnatish
log_info "Supervisor o'rnatilmoqda..."
apt install -y supervisor
systemctl enable supervisor
systemctl start supervisor
log_success "Supervisor o'rnatildi"

# 10. MySQL database yaratish
log_info "MySQL database yaratilmoqda..."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
log_success "MySQL database yaratildi"

# 11. Loyiha papkasini yaratish
log_info "Loyiha papkasi yaratilmoqda..."
mkdir -p $APP_DIR
cd $APP_DIR
log_success "Papka yaratildi: $APP_DIR"

# 12. Git clone
log_info "Loyiha yuklanmoqda..."
git clone $GIT_REPO .
log_success "Loyiha yuklandi"

# 13. Storage papkalarini yaratish
log_info "Storage papkalari yaratilmoqda..."
mkdir -p storage/logs
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
log_success "Storage papkalari yaratildi"

# 14. Composer install
log_info "Composer dependencies o'rnatilmoqda..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
log_success "Composer dependencies o'rnatildi"

# 15. NPM install va build
log_info "NPM dependencies o'rnatilmoqda..."
npm ci
log_success "NPM dependencies o'rnatildi"

log_info "Frontend build qilinmoqda..."
npm run build
log_success "Frontend build tayyor"

# 16. .env fayl yaratish
log_info ".env fayl yaratilmoqda..."
cp .env.example .env

# .env ni sozlash
sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
sed -i "s|APP_URL=http://localhost|APP_URL=https://${DOMAIN}|g" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|g" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|g" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|g" .env
sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|g" .env
sed -i "s|REDIS_HOST=.*|REDIS_HOST=127.0.0.1|g" .env
sed -i "s|CACHE_DRIVER=.*|CACHE_DRIVER=redis|g" .env
sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=redis|g" .env
sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=redis|g" .env

log_success ".env fayl yaratildi"

# 17. Application key
log_info "Application key generatsiya qilinmoqda..."
php artisan key:generate --force
log_success "Application key yaratildi"

# 18. Migratsiyalar
log_info "Migratsiyalar ishga tushirilmoqda..."
php artisan migrate --force
log_success "Migratsiyalar bajarildi"

# 19. Storage link
log_info "Storage link yaratilmoqda..."
php artisan storage:link
log_success "Storage link yaratildi"

# 20. Keshlarni yaratish
log_info "Keshlar yaratilmoqda..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
log_success "Keshlar yaratildi"

# 21. Ruxsatlarni sozlash
log_info "Fayl ruxsatlari sozlanmoqda..."
chown -R www-data:www-data $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
log_success "Ruxsatlar sozlandi"

# 22. Nginx konfiguratsiya
log_info "Nginx konfiguratsiya qilinmoqda..."
cat > /etc/nginx/sites-available/${DOMAIN} << 'NGINX_CONF'
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER www.DOMAIN_PLACEHOLDER;
    root APP_DIR_PLACEHOLDER/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    charset utf-8;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml application/atom+xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/phpPHP_VERSION_PLACEHOLDER-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
NGINX_CONF

# Placeholderlarni almashtirish
sed -i "s|DOMAIN_PLACEHOLDER|${DOMAIN}|g" /etc/nginx/sites-available/${DOMAIN}
sed -i "s|APP_DIR_PLACEHOLDER|${APP_DIR}|g" /etc/nginx/sites-available/${DOMAIN}
sed -i "s|PHP_VERSION_PLACEHOLDER|${PHP_VERSION}|g" /etc/nginx/sites-available/${DOMAIN}

# Saytni yoqish
ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Nginx test va restart
nginx -t
systemctl reload nginx
log_success "Nginx konfiguratsiya qilindi"

# 23. PHP-FPM sozlash
log_info "PHP-FPM sozlanmoqda..."
sed -i "s|user = www-data|user = www-data|g" /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
sed -i "s|group = www-data|group = www-data|g" /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
systemctl restart php${PHP_VERSION}-fpm
log_success "PHP-FPM sozlandi"

# 24. Supervisor konfiguratsiya
log_info "Supervisor konfiguratsiya qilinmoqda..."
cat > /etc/supervisor/conf.d/accmarket-worker.conf << SUPERVISOR_CONF
[program:accmarket-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR_CONF

supervisorctl reread
supervisorctl update
supervisorctl start accmarket-worker:*
log_success "Supervisor konfiguratsiya qilindi"

# 25. Cron job
log_info "Cron job qo'shilmoqda..."
(crontab -l 2>/dev/null; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -
log_success "Cron job qo'shildi"

# 26. Firewall
log_info "Firewall sozlanmoqda..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable
log_success "Firewall sozlandi"

# 27. SSL (Certbot)
log_info "SSL sertifikat o'rnatilmoqda..."
apt install -y certbot python3-certbot-nginx
certbot --nginx -d ${DOMAIN} --non-interactive --agree-tos --email admin@${DOMAIN} || log_warning "SSL o'rnatishda xatolik - keyinroq qo'lda o'rnating"
log_success "SSL o'rnatildi"

echo ""
echo "=========================================="
echo -e "${GREEN}   Server Setup Muvaffaqiyatli Yakunlandi!${NC}"
echo "=========================================="
echo ""
echo "Sayt: https://${DOMAIN}"
echo ""
echo "Keyingi qadamlar:"
echo "1. .env faylni tekshiring va to'ldiring: nano ${APP_DIR}/.env"
echo "2. MAIL sozlamalarini kiriting"
echo "3. Database seed qiling (ixtiyoriy): php artisan db:seed"
echo ""
echo "Foydali buyruqlar:"
echo "- Deploy: cd ${APP_DIR} && bash deploy-production.sh"
echo "- Loglar: tail -f ${APP_DIR}/storage/logs/laravel.log"
echo "- Queue: supervisorctl status"
echo ""
