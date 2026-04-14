<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Langues supportées
        $supportedLocales = ['fr', 'en', 'ar'];
        
        // Priorité : 1) URL segment, 2) Session, 3) Cookie, 4) Navigateur, 5) fr
        $locale = $request->segment(1);
        
        if (!in_array($locale, $supportedLocales)) {
            if (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales)) {
                $locale = Session::get('locale');
            } elseif ($request->hasCookie('locale') && in_array($request->cookie('locale'), $supportedLocales)) {
                $locale = $request->cookie('locale');
            } else {
                $locale = $this->getBrowserLocale($request);
            }
        }
        
        // Fallback vers le français si la langue n'est pas supportée
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'fr';
        }
        
        // Définir la locale pour l'application
        App::setLocale($locale);
        Session::put('locale', $locale);

        // Définir la locale par défaut pour la génération d'URLs (route())
        URL::defaults(['locale' => $locale]);
        
        /** @var Response $response */
        $response = $next($request);

        // Persister la locale dans un cookie (30 jours)
        $response->headers->setCookie(
            cookie('locale', $locale, 60 * 24 * 30, '/', null, false, false)
        );

        return $response;
    }
    
    /**
     * Détecter la langue du navigateur
     */
    private function getBrowserLocale(Request $request)
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if ($acceptLanguage) {
            $languages = explode(',', $acceptLanguage);
            foreach ($languages as $language) {
                $locale = substr(trim($language), 0, 2);
                if (in_array($locale, ['fr', 'en', 'ar'])) {
                    return $locale;
                }
            }
        }
        
        return 'fr'; // Fallback vers le français
    }
}