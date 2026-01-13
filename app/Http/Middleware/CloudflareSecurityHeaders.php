<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareSecurityHeaders
{
    /**
     * Handle an incoming request and add security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Strict-Transport-Security (HSTS) - only if HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy - skip in local development
        if (app()->environment('production')) {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://telegram.org",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net",
                "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:",
                "img-src 'self' data: https:",
                "connect-src 'self' https://nowpayments.io",
                "frame-src 'self' https://oauth.telegram.org",
                "frame-ancestors 'self'",
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
}
