<?php

if (!function_exists('route_prefix')) {
    /**
     * Retourne le préfixe de route selon le contexte (admin ou ctc)
     */
    function route_prefix(): string
    {
        return request()->routeIs('ctc.*') ? 'ctc' : 'admin';
    }
}

if (!function_exists('locale_url')) {
    /**
     * Génère l'URL pour changer de langue en conservant la page actuelle.
     * Gère /fr, /fr/, /fr/actualites, /fr/actualites?page=1, et /demande (sans préfixe).
     */
    function locale_url(string $locale): string
    {
        $uri = request()->getRequestUri();
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = parse_url($uri, PHP_URL_QUERY);
        $current = app()->getLocale();

        if ($current === $locale) {
            return $uri;
        }

        // Si l'URL contient déjà une locale (/fr ou /fr/xxx)
        if (preg_match('#^/(fr|en)(/|$)#', $path)) {
            $pattern = '#^/' . preg_quote($current, '#') . '(?=/|$)#';
            $newPath = preg_replace($pattern, '/' . $locale, $path);
            return $newPath . ($query ? '?' . $query : '');
        }

        // Si pas de locale (ex: /demande, /suivi), préfixer par la nouvelle locale
        $path = $path === '/' ? '' : $path;
        return '/' . $locale . $path . ($query ? '?' . $query : '');
    }
}
