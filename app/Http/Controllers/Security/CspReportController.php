<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Collecte les violations de Content-Security-Policy.
 *
 * Référencé par l'en-tête `Content-Security-Policy: report-uri /csp-violations`
 * dans `App\Http\Middleware\SecurityHeaders`.
 *
 * Les rapports sont écrits dans le canal de log `security` (storage/logs/security-*.log).
 */
class CspReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->json()->all() ?: json_decode($request->getContent(), true);

        Log::channel('security')->warning('CSP violation', [
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'report'     => $payload,
            'received_at' => now()->toIso8601String(),
        ]);

        return response()->noContent();
    }
}
