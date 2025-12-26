# Production Deployment Guide - AccMarket

## Tezkor boshlash

### 1. Serverga ulaning

```bash
ssh user@your-server-ip
cd /var/www/accmarket
```

### 2. Loyihani klonlash

```bash
git clone https://github.com/your-repo/accmarket.git .
```

### 3. Environment sozlash

```bash
cp .env.production .env
nano .env  # Barcha qiymatlarni to'ldiring
```

### 4. SSL sertifikatlari

```bash
mkdir -p docker/nginx/ssl
# SSL sertifikatlarini joylashtiring:
# - docker/nginx/ssl/fullchain.pem
# - docker/nginx/ssl/privkey.pem
```

### 5. Deploy qilish

```bash
chmod +x deploy.sh
./deploy.sh deploy
```

---

## Batafsil qo'llanma

### Talablar

- Docker 20.10+
- Docker Compose 2.0+
- Git
- Minimal 2GB RAM
- Minimal 20GB disk

### Server tayyorlash

```bash
# Docker o'rnatish
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Docker Compose o'rnatish
sudo apt-get update
sudo apt-get install docker-compose-plugin

# Foydalanuvchini docker guruhiga qo'shish
sudo usermod -aG docker $USER
```

### .env konfiguratsiyasi

```env
# Muhim o'zgaruvchilar
APP_KEY=base64:...                    # php artisan key:generate
APP_URL=https://yourdomain.com

# Database
DB_DATABASE=accmarket_prod
DB_USERNAME=accmarket_user
DB_PASSWORD=STRONG_PASSWORD_HERE
DB_ROOT_PASSWORD=STRONG_ROOT_PASSWORD

# Redis
REDIS_PASSWORD=STRONG_REDIS_PASSWORD

# Mail
MAIL_HOST=smtp.yourprovider.com
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

# NowPayments
NOWPAYMENTS_API_KEY=your_api_key
NOWPAYMENTS_IPN_SECRET=your_secret
NOWPAYMENTS_SANDBOX=false
```

### SSL Sertifikatlari

**Let's Encrypt bilan:**

```bash
# Certbot o'rnatish
sudo apt-get install certbot

# Sertifikat olish
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# Sertifikatlarni nusxalash
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/
```

### Deploy buyruqlari

| Buyruq | Tavsif |
|--------|--------|
| `./deploy.sh deploy` | To'liq deployment |
| `./deploy.sh update` | Faqat kod yangilash |
| `./deploy.sh rollback` | Oldingi versiyaga qaytish |
| `./deploy.sh status` | Holat ko'rish |
| `./deploy.sh logs` | Loglarni ko'rish |
| `./deploy.sh backup` | Backup yaratish |
| `./deploy.sh maintenance-on` | Maintenance rejim |
| `./deploy.sh maintenance-off` | Maintenance o'chirish |

---

## Docker konteynerlar

| Konteyner | Vazifasi | Port |
|-----------|----------|------|
| accmarket-php | PHP-FPM | 9000 |
| accmarket-nginx | Web server | 80, 443 |
| accmarket-mysql | Database | 3306 |
| accmarket-redis | Cache/Session | 6379 |
| accmarket-queue | Queue worker | - |
| accmarket-scheduler | Cron jobs | - |

---

## Monitoring

### Loglarni ko'rish

```bash
# Barcha loglar
./deploy.sh logs

# Faqat PHP loglar
./deploy.sh logs php

# Faqat Nginx loglar
./deploy.sh logs nginx
```

### Konteyner holati

```bash
./deploy.sh status
```

### Resurslarni kuzatish

```bash
docker stats
```

---

## Backup va Restore

### Backup yaratish

```bash
./deploy.sh backup
```

Backuplar `./backups/` papkasida saqlanadi.

### Manual restore

```bash
# Database restore
gunzip < backups/db_backup_YYYYMMDD_HHMMSS.sql.gz | \
  docker compose -f docker-compose.prod.yml exec -T mysql mysql -u root -p$DB_ROOT_PASSWORD $DB_DATABASE

# Storage restore
tar -xzf backups/storage_backup_YYYYMMDD_HHMMSS.tar.gz
```

---

## Xavfsizlik

### Firewall sozlash

```bash
# UFW bilan
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### Cloudflare orqali himoya

1. Domenni Cloudflare'ga qo'shing
2. SSL/TLS: Full (strict)
3. Always Use HTTPS: On
4. Bot Fight Mode: On

### Muntazam vazifalar

- [ ] Haftalik backup tekshiruvi
- [ ] Oylik SSL sertifikat yangilash
- [ ] Oylik Cloudflare IP ranges yangilash
- [ ] Security update'larni o'rnatish

---

## Muammolarni hal qilish

### Konteyner ishlamayapti

```bash
# Loglarni tekshiring
docker compose -f docker-compose.prod.yml logs php

# Konteynerni qayta ishga tushiring
docker compose -f docker-compose.prod.yml restart php
```

### Database ulanish xatosi

```bash
# MySQL konteyner ishlayotganini tekshiring
docker compose -f docker-compose.prod.yml ps mysql

# MySQL loglarini ko'ring
docker compose -f docker-compose.prod.yml logs mysql
```

### 502 Bad Gateway

```bash
# PHP-FPM ishlayotganini tekshiring
docker compose -f docker-compose.prod.yml exec php php-fpm -t

# Nginx konfiguratsiyasini tekshiring
docker compose -f docker-compose.prod.yml exec nginx nginx -t
```

### Sekin ishlayapti

```bash
# OPcache holatini tekshiring
docker compose -f docker-compose.prod.yml exec php php -i | grep opcache

# Cache tozalang
docker compose -f docker-compose.prod.yml exec php php artisan cache:clear
docker compose -f docker-compose.prod.yml exec php php artisan config:cache
```

---

## CI/CD Integration

### GitHub Actions misoli

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/accmarket
            ./deploy.sh update
```

---

## Yordam

- Cloudflare: https://support.cloudflare.com/
- Docker: https://docs.docker.com/
- Laravel: https://laravel.com/docs/

---

*Yaratilgan: 2025-yil, 26-dekabr*
