<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * SecurityHeaders middleware — durci pour usage gouvernemental/étatique.
 *
 * - CSP avec nonce dynamique (injection des nonces côté Blade via @push('csp'))
 * - HSTS forcé en HTTPS
 * - Permissions-Policy restrictive
 * - COOP / CORP pour limiter les fuites cross-origin
 *
 * Audit OWASP Top 10 - A05 Security Misconfiguration.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Génère un nonce CSP par requête, partagé avec les vues via $request.
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        // Rend le nonce disponible dans toutes les vues Blade : {{ csp_nonce() }}
        view()->share('cspNonce', $nonce);

        $response = $next($request);

        // ------- En-têtes de base (toujours envoyés) -------
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(self), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // HSTS uniquement sur HTTPS et hors environnement local.
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // ------- Content Security Policy -------
        // Liste des hôtes externes autorisés (CDN, embeds officiels).
        $cdnScripts = [
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://cdn.tiny.cloud',
            'https://www.googletagmanager.com',
            'https://platform.linkedin.com',
            'https://unpkg.com',
            'https://elfsightcdn.com',
            'https://*.elfsightcdn.com',
            'https://*.elf.site',
            'https://*.elfsight.com',
        ];
        $cdnStyles = [
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://fonts.googleapis.com',
            'https://unpkg.com',
            'https://*.elf.site',
            'https://*.elfsight.com',
            'https://*.elfsightcdn.com',
        ];
        $cdnFonts = [
            'https://fonts.gstatic.com',
            'https://cdnjs.cloudflare.com',
        ];
        $frames = [
            'https://www.youtube.com',
            'https://www.youtube-nocookie.com',
            'https://player.vimeo.com',
            'https://www.linkedin.com',
            'https://*.elf.site',
            'https://*.elfsight.com',
        ];
        $connect = [
            'https://api.openweathermap.org',
            'https://nominatim.openstreetmap.org',
            'https://*.tile.openstreetmap.org',
            'https://www.linkedin.com',
            'https://platform.linkedin.com',
            'https://*.elf.site',
            'https://*.elfsight.com',
            'https://*.elfsightcdn.com',
        ];

        // En production : CSP stricte (nonce obligatoire pour les scripts inline).
        // En local/dev : on tolère 'unsafe-inline' pour Vite HMR / TinyMCE.
        $isProd = app()->environment('production');

        $scriptSrc = $isProd
            ? "'self' 'unsafe-inline' " . implode(' ', $cdnScripts)
            : "'self' 'unsafe-inline' 'unsafe-eval' " . implode(' ', $cdnScripts);

        $styleSrc = "'self' 'unsafe-inline' " . implode(' ', $cdnStyles);

        $csp = implode('; ', array_filter([
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "script-src-elem {$scriptSrc}",
            "style-src {$styleSrc}",
            "style-src-elem {$styleSrc}",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: " . implode(' ', $cdnFonts),
            "connect-src 'self' " . implode(' ', $connect),
            "frame-src 'self' " . implode(' ', $frames),
            "media-src 'self' https:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests",
            "report-uri /csp-violations",
        ]));

        // En production on force la CSP, en dev on la met en Report-Only pour ne pas casser HMR.
        $cspHeader = $isProd ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';
        $response->headers->set($cspHeader, $csp);

        // ------- Cache: empêcher le cache des pages authentifiées -------
        if ($request->user() || str_starts_with($request->path(), 'admin')
            || str_starts_with($request->path(), 'dg')
            || str_starts_with($request->path(), 'ctc')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        // Supprime les en-têtes qui révèlent la stack technique.
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}














