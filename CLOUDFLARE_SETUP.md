# Cloudflare Protection Setup Guide

This application is now protected with Cloudflare middleware and security features.

## 🛡️ Security Features Implemented

### 1. **Trusted Proxies** (`TrustCloudflareProxies`)
- Automatically trusts all Cloudflare IP ranges (IPv4 and IPv6)
- Properly forwards client IP addresses through `CF-Connecting-IP` header
- Handles X-Forwarded headers correctly

### 2. **Security Headers** (`CloudflareSecurityHeaders`)
- **X-Content-Type-Options**: `nosniff` - Prevents MIME type sniffing
- **X-Frame-Options**: `SAMEORIGIN` - Prevents clickjacking attacks
- **X-XSS-Protection**: `1; mode=block` - Enables XSS filtering
- **Referrer-Policy**: `strict-origin-when-cross-origin` - Controls referrer information
- **Permissions-Policy**: Restricts geolocation, microphone, and camera access
- **HSTS**: `max-age=31536000` - Enforces HTTPS (when enabled)
- **Content-Security-Policy**: Prevents XSS and data injection attacks

### 3. **Request Verification** (`VerifyCloudflareRequest`)
- Verifies all requests come through Cloudflare (production only)
- Validates Cloudflare-specific headers (`CF-Ray`, `CF-Connecting-IP`)
- Blocks direct server access attempts
- Automatically disabled in local environment

### 4. **Rate Limiting** (`CloudflareRateLimiting`)
- Customizable per-route rate limiting
- Uses real client IP from Cloudflare headers
- Returns proper 429 responses with retry-after headers
- Includes rate limit information in response headers

---

## 📋 Cloudflare Dashboard Configuration

### Step 1: Add Your Domain to Cloudflare

1. Sign up at [cloudflare.com](https://www.cloudflare.com)
2. Click "Add a Site"
3. Enter your domain name
4. Select a plan (Free plan is sufficient for most features)
5. Cloudflare will scan your DNS records

### Step 2: Update DNS Records

1. Review the scanned DNS records
2. Ensure your domain's A/AAAA records point to your server IP
3. Enable Cloudflare proxy (orange cloud icon) for records you want protected
4. Update your domain's nameservers to Cloudflare's nameservers

### Step 3: Configure SSL/TLS Settings

**SSL/TLS** → **Overview**
- Set encryption mode to: **Full (strict)** or **Full**
- Full (strict) requires a valid SSL certificate on your origin server
- Full works with self-signed certificates

**SSL/TLS** → **Edge Certificates**
- ✅ Enable **Always Use HTTPS**
- ✅ Enable **Automatic HTTPS Rewrites**
- Set **Minimum TLS Version** to `TLS 1.2` or higher
- ✅ Enable **TLS 1.3**

### Step 4: Enable Security Features

**Security** → **Settings**
- **Security Level**: Medium or High
- ✅ Enable **Browser Integrity Check**
- ✅ Enable **Challenge Passage**

**Security** → **Bots**
- ✅ Enable **Bot Fight Mode** (Free) or **Super Bot Fight Mode** (Paid)

**Security** → **WAF** (Web Application Firewall)
- Create custom rules if needed (Paid plans)
- Enable managed rulesets (Paid plans)

### Step 5: Configure Firewall Rules (Optional)

**Security** → **WAF** → **Firewall Rules**

Example rules you can create:
```
Rule 1: Block countries
- If Country is in [list of countries to block]
- Then: Block

Rule 2: Rate limiting challenge
- If Rate > 100 requests per 10 minutes
- Then: Challenge

Rule 3: Protect admin area
- If URI Path starts with "/admin"
- And Country is not in [your country]
- Then: Challenge
```

### Step 6: Enable DDoS Protection

**Security** → **DDoS**
- DDoS protection is automatic and always on
- Configure sensitivity if needed (Paid plans)

### Step 7: Configure Page Rules (Optional)

**Rules** → **Page Rules**

Example rules:
```
Rule 1: Cache static assets
- URL: yoursite.com/build/*
- Cache Level: Cache Everything
- Edge Cache TTL: 1 month

Rule 2: Bypass cache for admin
- URL: yoursite.com/admin/*
- Cache Level: Bypass

Rule 3: Force HTTPS
- URL: http://yoursite.com/*
- Always Use HTTPS: On
```

### Step 8: Enable Cloudflare Analytics

**Analytics & Logs**
- Monitor traffic, threats, and performance
- Set up email alerts for security events

---

## 🔧 Application Configuration

### Environment Variables

Add to your `.env` file:

```env
# Cloudflare Settings
CLOUDFLARE_ENABLED=true

# Session Configuration (important for Cloudflare)
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Trust Cloudflare proxies
TRUSTED_PROXIES="*"
```

### Applying Middleware

#### Global Protection (Already Applied)
All web routes automatically have:
- ✅ Trusted proxies configuration
- ✅ Security headers

#### Optional: Add Request Verification
To enforce Cloudflare-only access on production, add to specific routes in `routes/web.php`:

```php
// Protect all routes (production only)
Route::middleware(['cloudflare.verify'])->group(function () {
    // Your routes here
});
```

#### Optional: Add Rate Limiting
Apply rate limiting to specific routes:

```php
// 60 requests per minute
Route::middleware(['cloudflare.throttle:60:1'])->group(function () {
    Route::post('/api/checkout', [CheckoutController::class, 'store']);
});

// 10 requests per minute for sensitive endpoints
Route::middleware(['cloudflare.throttle:10:1'])->group(function () {
    Route::post('/api/login', [AuthController::class, 'login']);
    Route::post('/api/register', [AuthController::class, 'register']);
});

// 100 requests per 5 minutes
Route::middleware(['cloudflare.throttle:100:5'])->group(function () {
    Route::get('/api/products', [ProductController::class, 'index']);
});
```

---

## 🧪 Testing Cloudflare Protection

### Test 1: Verify Headers
```bash
curl -I https://yourdomain.com
```

Look for these headers:
```
cf-ray: [unique-id]
cf-cache-status: [HIT/MISS/DYNAMIC]
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
x-xss-protection: 1; mode=block
strict-transport-security: max-age=31536000
```

### Test 2: Verify Real IP
Check that `$request->ip()` returns the real client IP, not Cloudflare's IP:

```php
// In any controller
dd($request->ip()); // Should show real client IP
dd($request->header('CF-Connecting-IP')); // Should match above
```

### Test 3: Test Rate Limiting
```bash
# Send 100 requests rapidly
for i in {1..100}; do
  curl https://yourdomain.com/api/endpoint
done
```

After exceeding the limit, you should receive:
```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

### Test 4: Verify SSL/TLS
```bash
curl -vI https://yourdomain.com 2>&1 | grep "SSL"
```

Should show TLS 1.2 or 1.3.

---

## 🚀 Performance Optimization

### Enable Cloudflare Caching

**Caching** → **Configuration**
- ✅ Enable **Caching**
- Set **Browser Cache TTL** to respect existing headers
- Enable **Always Online** for offline fallback

### Configure Cache Rules

1. Cache static assets (CSS, JS, images):
   - Automatically cached by Cloudflare
   - Served from edge locations worldwide

2. Cache API responses (optional):
   ```php
   // In your controller
   return response()->json($data)
       ->header('Cache-Control', 'public, max-age=300'); // 5 minutes
   ```

### Enable Auto Minify

**Speed** → **Optimization**
- ✅ Auto Minify: JavaScript, CSS, HTML
- ✅ Enable Brotli compression

### Enable Rocket Loader (Optional)

**Speed** → **Optimization**
- ✅ Enable **Rocket Loader** (may require testing with your app)

---

## 🔐 Additional Security Recommendations

### 1. IP Whitelist for Admin Area

Create a Firewall Rule:
```
If URI Path starts with "/admin"
And IP Address is not in [your IP addresses]
Then: Block
```

### 2. Country Blocking

Block high-risk countries:
```
If Country is in [list of countries]
Then: Block
```

### 3. Challenge for Suspicious Traffic

```
If Threat Score > 10
Then: Managed Challenge
```

### 4. Enable Email Security

**Email** → **Email Routing** (if using custom domain email)
- Enable SPF, DKIM, DMARC records

---

## 📊 Monitoring and Maintenance

### Regular Tasks

1. **Update Cloudflare IP Ranges** (Quarterly)
   - Visit: https://www.cloudflare.com/ips/
   - Update IP ranges in:
     - `TrustCloudflareProxies.php`
     - `VerifyCloudflareRequest.php`

2. **Review Security Events**
   - Check **Security** → **Events** weekly
   - Adjust firewall rules based on threats

3. **Monitor Analytics**
   - Review traffic patterns
   - Check for unusual spikes or attacks

4. **Test Rate Limits**
   - Periodically test that rate limiting works
   - Adjust limits based on actual usage

### Updating Cloudflare IP Ranges

```bash
# Fetch latest Cloudflare IPs
curl https://www.cloudflare.com/ips-v4
curl https://www.cloudflare.com/ips-v6

# Update the arrays in:
# - app/Http/Middleware/TrustCloudflareProxies.php
# - app/Http/Middleware/VerifyCloudflareRequest.php
```

---

## 🐛 Troubleshooting

### Issue: "Direct access not allowed" on Local Development

**Solution**: The `VerifyCloudflareRequest` middleware automatically disables in `local` environment. Ensure your `.env` has:
```env
APP_ENV=local
```

### Issue: Real IP Not Detected

**Solution**:
1. Verify Cloudflare proxy is enabled (orange cloud)
2. Check `CF-Connecting-IP` header is present
3. Ensure `TrustCloudflareProxies` middleware is loaded first

### Issue: Rate Limiting Too Aggressive

**Solution**: Adjust the rate limits in your route definitions:
```php
// Increase from 60:1 to 120:1
Route::middleware(['cloudflare.throttle:120:1'])->group(function () {
    // routes
});
```

### Issue: CSP Blocking Resources

**Solution**: Update CSP in `CloudflareSecurityHeaders.php`:
```php
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://your-cdn.com",
    // Add your trusted sources
];
```

### Issue: Infinite Redirect Loop

**Solution**:
1. Check SSL/TLS mode is set to **Full** or **Full (strict)**
2. Ensure origin server has valid SSL certificate
3. Verify HSTS header is only sent on HTTPS

---

## 📝 Summary

Your application now has comprehensive Cloudflare protection:

✅ **DDoS Protection** - Automatic and always-on
✅ **WAF** - Web Application Firewall
✅ **SSL/TLS Encryption** - End-to-end encryption
✅ **Bot Protection** - Block malicious bots
✅ **Rate Limiting** - Prevent abuse
✅ **Security Headers** - XSS, clickjacking protection
✅ **Real IP Detection** - Accurate client IP tracking
✅ **Request Verification** - Cloudflare-only access
✅ **Caching** - Global CDN for performance

**Next Steps:**
1. Add your domain to Cloudflare
2. Configure DNS and SSL settings
3. Enable security features in dashboard
4. Test the implementation
5. Monitor and adjust as needed

For support: https://support.cloudflare.com/
