<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * Changer la langue de l'application
     */
    public function setLocale($locale)
    {
        // Langues supportées
        $supportedLocales = ['fr', 'en', 'ar'];
        
        // Vérifier si la langue est supportée
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'fr'; // Fallback vers le français
        }
        
        // Définir la locale pour l'application
        App::setLocale($locale);
        
        // Sauvegarder dans la session
        Session::put('locale', $locale);
        
        // Rediriger vers la page précédente ou l'accueil
        $previousUrl = url()->previous();
        
        // Si l'URL précédente contient une langue, la remplacer
        if (preg_match('/\/(fr|en|ar)\//', $previousUrl)) {
            $newUrl = preg_replace('/\/(fr|en|ar)\//', '/' . $locale . '/', $previousUrl);
        } else {
            // Si pas de langue dans l'URL, ajouter la nouvelle langue
            $baseUrl = url('/');
            $path = str_replace($baseUrl, '', $previousUrl);
            $newUrl = $baseUrl . '/' . $locale . $path;
        }
        
        return redirect($newUrl);
    }
}
