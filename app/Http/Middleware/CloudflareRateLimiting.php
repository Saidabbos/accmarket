<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareRateLimiting
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $limit = '60:1'): Response
    {
        // Parse limit (format: "requests:minutes")
        [$maxAttempts, $decayMinutes] = explode(':', $limit);

        // Use Cloudflare's real IP if available, otherwise fall back to request IP
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, (int) $maxAttempts)) {
            return $this->buildRateLimitResponse($key, (int) $maxAttempts);
        }

        $this->limiter->hit($key, (int) $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key, (int) $maxAttempts);
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use Cloudflare's connecting IP if available
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();

        // Include route to allow different limits per endpoint
        $route = $request->route()?->getName() ?? $request->path();

        return sha1($ip . '|' . $route);
    }

    /**
     * Build a rate limit exceeded response.
     */
    protected function buildRateLimitResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429, [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Add rate limit headers to the response.
     */
    protected function addRateLimitHeaders(Response $response, string $key, int $maxAttempts): Response
    {
        $remaining = $this->limiter->remaining($key, $maxAttempts);
        $retryAfter = $this->limiter->availableIn($key);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));

        if ($remaining === 0) {
            $response->headers->set('Retry-After', $retryAfter);
        }

        return $response;
    }
}
