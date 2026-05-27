<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API endpoints qui nécessitent une authentification alternative
        'api/*',
        // Webhooks externes (si nécessaire)
        'webhooks/*',
        // Payment callbacks (PayDunya, PayPal)
        '*/faire-un-don/callback',
        '*/paypal/webhook',
    ];
}
