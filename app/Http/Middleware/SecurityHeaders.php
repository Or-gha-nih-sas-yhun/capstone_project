<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Injects security-hardening HTTP response headers on every request.
 *
 * Headers applied:
 *  - X-Content-Type-Options     Prevents MIME-type sniffing
 *  - X-Frame-Options            Prevents clickjacking
 *  - X-XSS-Protection           Browser XSS filter (legacy, belt-and-suspenders)
 *  - Referrer-Policy            Limits referrer leakage
 *  - Permissions-Policy         Locks down sensitive browser APIs
 *  - Strict-Transport-Security  HTTPS enforcement (production only)
 *  - Content-Security-Policy    Restricts resource origins
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Block iframe embedding from other origins
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Legacy XSS filter (still respected by some older browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Limit referrer information sent to third parties
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Lock down Permissions — only allow geolocation on self (needed for admin location capture)
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(self), camera=(), microphone=(), payment=(), usb=()'
        );

        // HSTS — only set in production over HTTPS
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content-Security-Policy
        // Allows: self, Google reCAPTCHA, Google Fonts, CDN assets, inline scripts (needed for Blade)
        // Own-domain hosts are derived from the deployment's config so this
        // works for any barangay without editing the middleware. The admin
        // subdomain serves /assets by redirecting to the main domain, so the
        // main domain must be allowed for scripts, styles and fonts too.
        $domain = config('barangay.domain');
        $adminDomain = config('barangay.admin_domain');
        $appHosts = collect([$domain, $adminDomain])
            ->filter()
            ->map(fn ($host) => 'https://' . $host)
            ->unique()
            ->implode(' ');

        $csp = implode('; ', [
            "default-src 'self'",
            trim("script-src 'self' 'unsafe-inline' 'unsafe-eval' {$appHosts} https://www.google.com https://www.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com"),
            trim("style-src 'self' 'unsafe-inline' {$appHosts} https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com"),
            trim("font-src 'self' {$appHosts} https://fonts.gstatic.com https://cdnjs.cloudflare.com data:"),
            // Map tiles for the admin Plot Mapping page
            trim("img-src 'self' data: blob: {$appHosts} https://www.google.com https://*.tile.openstreetmap.org https://*.tile.opentopomap.org https://server.arcgisonline.com https://unpkg.com"),
            "frame-src https://www.google.com",
            // PSGC address lookups on the admin Settings page
            "connect-src 'self' https://www.google.com https://psgc.gitlab.io",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
