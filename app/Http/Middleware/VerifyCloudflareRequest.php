<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCloudflareRequest
{
    /**
     * Cloudflare IP ranges for verification.
     * These should be updated periodically from https://www.cloudflare.com/ips/
     */
    protected array $cloudflareIpv4 = [
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
    ];

    protected array $cloudflareIpv6 = [
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification in local environment
        if (app()->environment('local')) {
            return $next($request);
        }

        // Get the client IP (should be Cloudflare's IP if behind CF)
        $clientIp = $request->server('REMOTE_ADDR');

        // Verify the request is coming from Cloudflare
        if (!$this->isCloudflareIp($clientIp)) {
            abort(403, 'Direct access not allowed. Please use Cloudflare.');
        }

        // Verify Cloudflare headers are present
        if (!$request->header('CF-Ray') || !$request->header('CF-Connecting-IP')) {
            abort(403, 'Invalid Cloudflare headers.');
        }

        return $next($request);
    }

    /**
     * Check if an IP address is within Cloudflare's IP ranges.
     */
    protected function isCloudflareIp(string $ip): bool
    {
        // Check IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            foreach ($this->cloudflareIpv4 as $range) {
                if ($this->ipInRange($ip, $range)) {
                    return true;
                }
            }
        }

        // Check IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            foreach ($this->cloudflareIpv6 as $range) {
                if ($this->ipv6InRange($ip, $range)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if an IPv4 address is within a CIDR range.
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);

        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    /**
     * Check if an IPv6 address is within a CIDR range.
     */
    protected function ipv6InRange(string $ip, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);

        $ip = inet_pton($ip);
        $subnet = inet_pton($subnet);
        $bits = (int) $bits;

        $bytesToCheck = (int) ($bits / 8);
        $bitsInLastByte = $bits % 8;

        for ($i = 0; $i < $bytesToCheck; $i++) {
            if ($ip[$i] !== $subnet[$i]) {
                return false;
            }
        }

        if ($bitsInLastByte > 0) {
            $mask = 0xFF << (8 - $bitsInLastByte);
            if ((ord($ip[$bytesToCheck]) & $mask) !== (ord($subnet[$bytesToCheck]) & $mask)) {
                return false;
            }
        }

        return true;
    }
}
