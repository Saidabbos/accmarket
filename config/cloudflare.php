<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Protection Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable Cloudflare protection features. When disabled in local
    | environment, verification middleware will automatically skip checks.
    |
    */

    'enabled' => env('CLOUDFLARE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cloudflare IP Ranges
    |--------------------------------------------------------------------------
    |
    | Cloudflare's IP ranges for proxy verification. These should be updated
    | periodically from: https://www.cloudflare.com/ips/
    |
    | Last updated: December 2025
    |
    */

    'ip_ranges' => [
        'ipv4' => [
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/13',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22',
        ],

        'ipv6' => [
            '2400:cb00::/32',
            '2606:4700::/32',
            '2803:f800::/32',
            '2405:b500::/32',
            '2405:8100::/32',
            '2a06:98c0::/29',
            '2c0f:f248::/32',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Defaults
    |--------------------------------------------------------------------------
    |
    | Default rate limiting settings for the CloudflareRateLimiting middleware.
    | These can be overridden per-route using middleware parameters.
    |
    */

    'rate_limiting' => [
        // Default: 60 requests per 1 minute
        'default' => [
            'requests' => 60,
            'minutes' => 1,
        ],

        // Preset configurations for common scenarios
        'presets' => [
            'strict' => '10:1',      // Login, register, password reset
            'moderate' => '60:1',     // General authenticated routes
            'relaxed' => '120:1',     // Public browsing
            'api_read' => '200:1',    // API read operations
            'api_write' => '20:1',    // API write operations
            'critical' => '5:1',      // Withdrawals, refunds, critical actions
            'download' => '50:1',     // File downloads
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security headers that will be applied to all responses.
    |
    */

    'security_headers' => [
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'SAMEORIGIN',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',

        // HSTS (only applied on HTTPS)
        'hsts' => [
            'enabled' => true,
            'max_age' => 31536000, // 1 year
            'include_subdomains' => true,
            'preload' => true,
        ],

        // Content Security Policy
        'csp' => [
            'enabled' => true,
            'directives' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
                'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com",
                'font-src' => "'self' https://fonts.gstatic.com data:",
                'img-src' => "'self' data: https:",
                'connect-src' => "'self'",
                'frame-ancestors' => "'self'",
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Headers
    |--------------------------------------------------------------------------
    |
    | Configure which Cloudflare headers to use for various purposes.
    |
    */

    'headers' => [
        // Header containing the real client IP
        'connecting_ip' => 'CF-Connecting-IP',

        // Header containing the unique request identifier
        'ray' => 'CF-Ray',

        // Header containing the visitor's country code
        'country' => 'CF-IPCountry',

        // Header indicating if the visitor is using Tor
        'tor' => 'CF-Ipcountry',

        // Header containing the visitor's device type
        'device_type' => 'CF-Device-Type',
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the VerifyCloudflareRequest middleware.
    |
    */

    'verification' => [
        // Require Cloudflare headers (CF-Ray, CF-Connecting-IP)
        'require_headers' => true,

        // Verify IP is from Cloudflare range
        'verify_ip_range' => true,

        // Environments where verification should be skipped
        'skip_environments' => ['local', 'testing'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which headers to trust from Cloudflare proxies.
    |
    */

    'trusted_proxies' => [
        'headers' => [
            'forwarded_for' => true,
            'forwarded_host' => true,
            'forwarded_port' => true,
            'forwarded_proto' => true,
            'forwarded_aws_elb' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Cloudflare-specific caching behavior.
    |
    */

    'cache' => [
        // Add Cloudflare cache headers to static assets
        'static_assets' => [
            'enabled' => true,
            'max_age' => 2592000, // 30 days
            'extensions' => ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'woff', 'woff2'],
        ],

        // API response caching
        'api_responses' => [
            'enabled' => false,
            'max_age' => 300, // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for Cloudflare-related events.
    |
    */

    'logging' => [
        // Log blocked requests
        'log_blocked_requests' => true,

        // Log rate limit hits
        'log_rate_limits' => true,

        // Log Cloudflare header information
        'log_cloudflare_headers' => env('APP_DEBUG', false),
    ],

];
