# Cloudflare Protection - Quick Start Guide

## ⚡ 5-Minute Setup

### 1. Add Domain to Cloudflare
1. Go to [cloudflare.com](https://www.cloudflare.com) and sign up
2. Click **"Add a Site"** and enter your domain
3. Select the **Free plan**
4. Cloudflare will scan your DNS records

### 2. Update Nameservers
Copy the nameservers provided by Cloudflare and update them at your domain registrar.

Example:
```
ns1.cloudflare.com
ns2.cloudflare.com
```

### 3. Configure SSL/TLS
Go to **SSL/TLS** → **Overview**
- Set encryption mode: **Full** or **Full (strict)**

Enable these settings:
- ✅ **Always Use HTTPS**
- ✅ **Automatic HTTPS Rewrites**
- ✅ **TLS 1.3**

### 4. Enable Security Features

**Security** → **Settings**
- Security Level: **Medium** or **High**
- ✅ **Browser Integrity Check**
- ✅ **Challenge Passage**

**Security** → **Bots**
- ✅ **Bot Fight Mode**

### 5. Update Your `.env` File

```env
# For Production (with HTTPS)
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Cloudflare Protection
CLOUDFLARE_ENABLED=true
TRUSTED_PROXIES="*"
```

### 6. Clear Cache and Test

```bash
# Clear Laravel cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Test your site
curl -I https://yourdomain.com
```

Look for these headers in the response:
```
cf-ray: [unique-id]
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
strict-transport-security: max-age=31536000
```

---

## 🎯 What's Already Protected

Your application now has:

### ✅ Automatic Protection (Applied to All Routes)
- **Trusted Cloudflare Proxies** - Real IP detection
- **Security Headers** - XSS, clickjacking, MIME-sniffing protection
- **SSL/TLS Encryption** - End-to-end encryption
- **DDoS Protection** - Automatic Cloudflare protection
- **Bot Protection** - Block malicious bots

### 🔧 Optional Protection (Apply as Needed)

#### Rate Limiting
Add to specific routes in `routes/web.php`:

```php
// Protect login (10 requests per minute)
Route::middleware(['cloudflare.throttle:10:1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protect checkout (30 requests per minute)
Route::middleware(['cloudflare.throttle:30:1'])->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'process']);
});

// Protect API (100 requests per minute)
Route::middleware(['cloudflare.throttle:100:1'])->group(function () {
    Route::get('/api/products', [ProductController::class, 'index']);
});
```

#### Cloudflare Verification (Production Only)
Block direct server access - only allow through Cloudflare:

```php
// In routes/web.php
Route::middleware(['cloudflare.verify'])->group(function () {
    // Your protected routes
});
```

**Note**: This middleware automatically skips in `local` environment.

---

## 📝 Common Rate Limit Configurations

Copy these to your `routes/web.php`:

```php
// Authentication (strict)
Route::middleware(['cloudflare.throttle:10:1'])->group(function () {
    Route::post('/login', ...);
    Route::post('/register', ...);
    Route::post('/forgot-password', ...);
});

// Checkout & Payments (moderate)
Route::middleware(['cloudflare.throttle:30:1'])->group(function () {
    Route::post('/checkout', ...);
    Route::post('/payment', ...);
});

// Public Browsing (relaxed)
Route::middleware(['cloudflare.throttle:120:1'])->group(function () {
    Route::get('/products', ...);
    Route::get('/categories', ...);
});

// Admin Area (moderate + verification)
Route::middleware(['cloudflare.verify', 'cloudflare.throttle:60:1'])
    ->prefix('admin')
    ->group(function () {
        // Admin routes
    });

// Downloads (moderate)
Route::middleware(['cloudflare.throttle:50:1'])->group(function () {
    Route::get('/download/{order}/{item}', ...);
});

// Critical Actions (very strict)
Route::middleware(['cloudflare.throttle:5:1'])->group(function () {
    Route::post('/withdraw', ...);
    Route::post('/refund', ...);
});
```

---

## 🔒 Additional Security (Optional but Recommended)

### IP Whitelist for Admin Area

In **Cloudflare Dashboard** → **Security** → **WAF** → **Custom Rules**:

```
Rule Name: Protect Admin Area
If: URI Path starts with "/admin" AND IP Address is not in [Your IP]
Then: Block
```

### Country Blocking

Block high-risk countries:

```
Rule Name: Block Countries
If: Country is in [list of countries]
Then: Block
```

### Challenge Suspicious Traffic

```
Rule Name: Challenge Threats
If: Threat Score > 10
Then: Managed Challenge
```

---

## 🐛 Troubleshooting

### Issue: "Direct access not allowed" on Local

**Solution**: Set in `.env`:
```env
APP_ENV=local
```

The verification middleware automatically disables in local environment.

### Issue: Infinite Redirect

**Solution**:
1. Check SSL/TLS mode is **Full** or **Full (strict)**
2. Ensure origin server has SSL certificate
3. Set in `.env`:
```env
SESSION_SECURE_COOKIE=true
```

### Issue: Real IP Not Detected

**Solution**: Ensure orange cloud ☁️ is enabled in Cloudflare DNS settings.

### Issue: Rate Limiting Too Strict

**Solution**: Increase limits in routes:
```php
// From 60:1 to 120:1
Route::middleware(['cloudflare.throttle:120:1'])->group(function () {
    // routes
});
```

---

## 📊 Monitor Your Protection

### Cloudflare Dashboard
- **Analytics** → See traffic and threats blocked
- **Security** → **Events** → Review security events
- **Speed** → Monitor performance improvements

### Test Rate Limiting
```bash
# Send 100 requests
for i in {1..100}; do curl https://yourdomain.com/api/endpoint; done
```

After hitting the limit:
```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

### Test Security Headers
```bash
curl -I https://yourdomain.com
```

---

## 📚 Full Documentation

For detailed setup, configuration options, and advanced features, see:
- **[CLOUDFLARE_SETUP.md](CLOUDFLARE_SETUP.md)** - Complete setup guide
- **[routes/web.example.cloudflare.php](routes/web.example.cloudflare.php)** - Route examples
- **[config/cloudflare.php](config/cloudflare.php)** - Configuration reference

---

## ✅ Checklist

- [ ] Domain added to Cloudflare
- [ ] Nameservers updated
- [ ] SSL/TLS configured (Full or Full strict)
- [ ] Security features enabled
- [ ] `.env` updated with Cloudflare settings
- [ ] Cache cleared (`php artisan config:clear`)
- [ ] Tested site is accessible
- [ ] Verified headers are present (`curl -I`)
- [ ] Applied rate limiting to critical routes
- [ ] (Optional) Added IP whitelist for admin
- [ ] (Optional) Configured firewall rules

---

## 🚀 You're Protected!

Your application is now protected by Cloudflare with:
- ✅ DDoS protection
- ✅ Bot filtering
- ✅ SSL/TLS encryption
- ✅ Security headers
- ✅ Real IP detection
- ✅ Ready for rate limiting

**Need Help?**
- Cloudflare Support: https://support.cloudflare.com/
- Full Guide: [CLOUDFLARE_SETUP.md](CLOUDFLARE_SETUP.md)
