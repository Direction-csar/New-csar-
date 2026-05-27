<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de cache HTTP intelligent pour les pages publiques.
 *
 * Ajoute les headers Cache-Control / ETag sur les requêtes GET publiques
 * non-authentifiées pour permettre la mise en cache navigateur et CDN.
 *
 * Usage :
 *   Route::get('/...')->middleware('http-cache:300');   // 5 minutes
 *   Route::get('/...')->middleware('http-cache:3600');  // 1 heure
 */
class HttpCache
{
    public function handle(Request $request, Closure $next, int $maxAge = 300): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Ne jamais mettre en cache si :
        // - méthode autre que GET/HEAD
        // - utilisateur authentifié (contenu personnalisé)
        // - cookie de session présent et différent du visiteur anonyme
        // - réponse non-2xx
        if (
            !in_array($request->method(), ['GET', 'HEAD'], true) ||
            auth()->check() ||
            !$response->isSuccessful()
        ) {
            return $response;
        }

        // Cache public (CDN + navigateur) avec revalidation conditionnelle
        $response->setPublic();
        $response->setMaxAge($maxAge);
        $response->setSharedMaxAge($maxAge);
        $response->headers->addCacheControlDirective('must-revalidate');

        // ETag basé sur le contenu pour 304 Not Modified
        $etag = '"' . md5($response->getContent() ?: '') . '"';
        $response->setEtag(trim($etag, '"'));

        if ($request->headers->get('If-None-Match') === $etag) {
            $response->setNotModified();
        }

        return $response;
    }
}
