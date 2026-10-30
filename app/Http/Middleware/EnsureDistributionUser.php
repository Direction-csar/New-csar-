<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * API mobile Distribution / Scanner : le jeton Sanctum doit appartenir a un
 * membre du personnel (table users) actif, et ne pas avoir depasse sa duree de vie.
 *
 * - un jeton de collecteur SIM (autre table, memes identifiants numeriques) est refuse ;
 * - un compte desactive apres sa connexion perd immediatement l'acces ;
 * - un jeton trop ancien est revoque (401) : l'application invite a se reconnecter.
 */
class EnsureDistributionUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof User) {
            return $this->deny('Accès réservé au personnel du CSAR.', 403);
        }

        if (!$user->is_active) {
            return $this->deny('Votre compte a été désactivé.', 403);
        }

        $token = $user->currentAccessToken();
        $ttlDays = (int) config('distribution.token_ttl_days', 30);

        if ($ttlDays > 0 && $token instanceof PersonalAccessToken
            && $token->created_at && $token->created_at->lt(now()->subDays($ttlDays))) {
            $token->delete();
            return $this->deny('Session expirée. Veuillez vous reconnecter.', 401);
        }

        return $next($request);
    }

    private function deny(string $message, int $status): Response
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
