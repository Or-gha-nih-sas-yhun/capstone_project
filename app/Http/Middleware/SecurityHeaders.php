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
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:",
            "img-src 'self' data: https://brgypilieclearance.com https://admin.brgypilieclearance.com https://www.google.com",
            "frame-src https://www.google.com",
            "connect-src 'self' https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
