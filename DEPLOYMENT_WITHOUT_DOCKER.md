# AccMarket - Digital Ocean Dropletga Docker'siz Deploy Qilish

Bu qo'llanma AccMarket loyihasini Digital Ocean dropletga Docker ishlatmasdan deploy qilishni ko'rsatadi.

## Talablar

- Digital Ocean Droplet (Ubuntu 22.04 LTS tavsiya etiladi)
- Minimal: 1GB RAM, 1 vCPU, 25GB SSD
- Tavsiya: 2GB RAM, 1 vCPU, 50GB SSD

## 1-Qadam: Droplet Yaratish

1. Digital Ocean dashboard'ga kiring
2. "Create" > "Droplets" bosing
3. Ubuntu 22.04 LTS tanlang
4. O'lchamni tanlang (minimum $6/oy)
5. SSH kalitingizni qo'shing
6. "Create Droplet" bosing

## 2-Qadam: Serverga Ulanish

```bash
ssh root@your_droplet_ip
```

## 3-Qadam: Tizimni Yangilash

```bash
apt update && apt upgrade -y
```

## 4-Qadam: Kerakli Dasturlarni O'rnatish

**MUHIM:** Quyidagi buyruqlarni ketma-ket, tartib bilan bajaring!

### 4.1 PHP 8.2 va Kengaytmalarini O'rnatish (BIRINCHI!)

```bash
# PHP repository qo'shish
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

# PHP 8.2 va kengaytmalarini o'rnatish
apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl php8.2-readline php8.2-redis

# PHP o'rnatilganini tekshirish
php -v
```

### 4.2 Nginx O'rnatish

```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

### 4.3 MySQL O'rnatish

```bash
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

# MySQL xavfsizligini sozlash
mysql_secure_installation
```

### 4.4 Redis O'rnatish (Sessiya va Cache uchun)

```bash
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server
```

### 4.5 Composer O'rnatish (PHP o'rnatilgandan keyin!)

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Composer o'rnatilganini tekshirish
composer --version
```

### 4.6 Node.js va NPM O'rnatish

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Node.js o'rnatilganini tekshirish
node -v
npm -v
```

### 4.7 Git O'rnatish

```bash
apt install -y git

# Git o'rnatilganini tekshirish
git --version
```

## 5-Qadam: Foydalanuvchi Yaratish

```bash
# www-data guruhiga yangi foydalanuvchi qo'shish
adduser deployer
usermod -aG www-data deployer
usermod -aG sudo deployer

# deployer foydalanuvchisiga o'tish
su - deployer
```

## 6-Qadam: MySQL Ma'lumotlar Bazasini Yaratish

```bash
sudo mysql -u root -p
```

MySQL ichida:

```sql
CREATE DATABASE accmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'accmarket'@'localhost' IDENTIFIED BY 'kuchli_parol_kiriting';
GRANT ALL PRIVILEGES ON accmarket.* TO 'accmarket'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 7-Qadam: Loyihani Klonlash

```bash
cd /var/www
sudo mkdir accmarket
sudo chown deployer:www-data accmarket
cd accmarket

# Git orqali klonlash
git clone https://github.com/Saidabbos/accmarket.git .

# Yoki loyihani yuklab olish va ko'chirish
```

## 8-Qadam: Laravel Sozlamalari

### Composer Dependensiyalarini O'rnatish

```bash
composer install --optimize-autoloader --no-dev
```

### NPM Dependensiyalarini O'rnatish va Build

```bash
npm install
npm run build
```

### .env Faylini Sozlash

```bash
cp .env.example .env
nano .env
```

`.env` faylini quyidagicha sozlang:

```env
APP_NAME=AccMarket
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=accmarket
DB_USERNAME=accmarket
DB_PASSWORD=kuchli_parol_kiriting

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ADMIN_ADDRESS=admin@your-domain.com
```

### Application Key Generatsiya

```bash
php artisan key:generate
```

### Migratsiyalarni Ishga Tushirish

```bash
php artisan migrate --force
```

### Seedlarni Ishga Tushirish (Ixtiyoriy)

```bash
php artisan db:seed --force
```

### Keshlarni Yaratish

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Storage Link

```bash
php artisan storage:link
```

## 9-Qadam: Papka Ruxsatlarini Sozlash

```bash
sudo chown -R deployer:www-data /var/www/accmarket
sudo chmod -R 775 /var/www/accmarket/storage
sudo chmod -R 775 /var/www/accmarket/bootstrap/cache
```

## 10-Qadam: Nginx Konfiguratsiyasi

```bash
sudo nano /etc/nginx/sites-available/accmarket
```

Quyidagini yozing:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/accmarket/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;

    charset utf-8;

    # Gzip siqish
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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static fayllar uchun cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

Saytni yoqish:

```bash
sudo ln -s /etc/nginx/sites-available/accmarket /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

## 11-Qadam: PHP-FPM Sozlamalari

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Quyidagi qatorlarni toping va o'zgartiring:

```ini
user = deployer
group = www-data
listen.owner = deployer
listen.group = www-data
```

PHP-FPM ni qayta ishga tushiring:

```bash
sudo systemctl restart php8.2-fpm
```

## 12-Qadam: SSL Sertifikati (Let's Encrypt)

```bash
# Certbot o'rnatish
sudo apt install -y certbot python3-certbot-nginx

# SSL sertifikat olish
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Avtomatik yangilashni tekshirish
sudo certbot renew --dry-run
```

## 13-Qadam: Firewall Sozlamalari

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

## 14-Qadam: Queue Worker (Supervisor)

```bash
sudo apt install -y supervisor
```

Supervisor konfiguratsiyasi:

```bash
sudo nano /etc/supervisor/conf.d/accmarket-worker.conf
```

```ini
[program:accmarket-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/accmarket/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deployer
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/accmarket/storage/logs/worker.log
stopwaitsecs=3600
```

Supervisor ni qayta yuklash:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start accmarket-worker:*
```

## 15-Qadam: Cron Job (Task Scheduler)

```bash
crontab -e
```

Quyidagi qatorni qo'shing:

```
* * * * * cd /var/www/accmarket && php artisan schedule:run >> /dev/null 2>&1
```

## 16-Qadam: Deploy Skripti

`/var/www/accmarket/deploy.sh` faylini yarating:

```bash
nano /var/www/accmarket/deploy.sh
```

```bash
#!/bin/bash
set -e

echo "🚀 Deployment boshlandi..."

cd /var/www/accmarket

# Maintenance mode
php artisan down --retry=60

# Git pull
echo "📥 Yangilanishlarni yuklab olish..."
git pull origin master

# Composer
echo "📦 Composer dependensiyalarini yangilash..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# NPM
echo "🔨 Frontend build..."
npm ci
npm run build

# Migratsiyalar
echo "🗄️ Migratsiyalarni ishga tushirish..."
php artisan migrate --force

# Cache
echo "🧹 Keshlarni tozalash va yaratish..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Queue restart
echo "🔄 Queue workerni qayta ishga tushirish..."
php artisan queue:restart

# Ruxsatlar
echo "🔐 Ruxsatlarni sozlash..."
sudo chown -R deployer:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Maintenance mode off
php artisan up

echo "✅ Deployment muvaffaqiyatli yakunlandi!"
```

Skriptni executable qilish:

```bash
chmod +x /var/www/accmarket/deploy.sh
```

## Yangilash Jarayoni

Yangilanishlarni deploy qilish uchun:

```bash
cd /var/www/accmarket
./deploy.sh
```

## Foydali Buyruqlar

### Loglarni Ko'rish

```bash
# Laravel loglar
tail -f /var/www/accmarket/storage/logs/laravel.log

# Nginx error log
tail -f /var/log/nginx/error.log

# PHP-FPM log
tail -f /var/log/php8.2-fpm.log
```

### Xizmatlarni Qayta Ishga Tushirish

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql
sudo systemctl restart redis-server
sudo supervisorctl restart accmarket-worker:*
```

### Keshlarni Tozalash

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Ma'lumotlar Bazasi Backup

```bash
# Backup yaratish
mysqldump -u accmarket -p accmarket > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup ni tiklash
mysql -u accmarket -p accmarket < backup_file.sql
```

## Muammolarni Hal Qilish

### 500 Internal Server Error

1. Laravel loglarni tekshiring
2. Papka ruxsatlarini tekshiring
3. `.env` faylini tekshiring

### 502 Bad Gateway

1. PHP-FPM ishlayotganini tekshiring: `sudo systemctl status php8.2-fpm`
2. Nginx socket yo'lini tekshiring

### Permission Denied

```bash
sudo chown -R deployer:www-data /var/www/accmarket
sudo chmod -R 775 /var/www/accmarket/storage
sudo chmod -R 775 /var/www/accmarket/bootstrap/cache
```

### Redis Ulanish Xatosi

```bash
sudo systemctl status redis-server
sudo systemctl restart redis-server
```

## Xavfsizlik Tavsiyanomasi

1. Root login'ni o'chiring (`/etc/ssh/sshd_config` da `PermitRootLogin no`)
2. SSH portini o'zgartiring
3. Fail2ban o'rnating: `sudo apt install fail2ban`
4. Muntazam yangilanishlar: `sudo apt update && sudo apt upgrade`
5. Kuchli parollar ishlating
6. `.env` faylini git'ga qo'shmang

## Server Monitoringi

### Resurslarni Tekshirish

```bash
# CPU va RAM
htop

# Disk joy
df -h

# MySQL holatini
mysqladmin -u root -p status
```

### Xizmatlar Holati

```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo supervisorctl status
```

---

**Muallif:** AccMarket Development Team
**Versiya:** 1.0
**Sana:** 2025-yil
