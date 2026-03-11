<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Service d'optimisation SEO pour la plateforme publique
 */
class SeoService
{
    /**
     * Générer les meta tags pour une page
     */
    public static function generateMetaTags($title, $description = null, $image = null, $type = 'website', $url = null)
    {
        $siteName = 'CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience';
        $defaultDescription = 'Institution publique dédiée à la sécurité alimentaire et à la résilience au Sénégal.';
        $defaultImage = asset('images/csar-og-image.jpg');
        
        $description = $description ?? $defaultDescription;
        $image = $image ?? $defaultImage;
        $url = $url ?? url()->current();
        
        $meta = [];
        
        // Basic Meta Tags
        $meta['title'] = $title . ' - CSAR';
        $meta['description'] = Str::limit(strip_tags($description), 160);
        
        // Open Graph (Facebook, LinkedIn)
        $meta['og:site_name'] = $siteName;
        $meta['og:title'] = $title;
        $meta['og:description'] = Str::limit(strip_tags($description), 200);
        $meta['og:type'] = $type;
        $meta['og:url'] = $url;
        $meta['og:image'] = $image;
        $meta['og:locale'] = 'fr_SN';
        
        // Twitter Cards
        $meta['twitter:card'] = 'summary_large_image';
        $meta['twitter:title'] = $title;
        $meta['twitter:description'] = Str::limit(strip_tags($description), 200);
        $meta['twitter:image'] = $image;
        
        // Additional SEO
        $meta['robots'] = 'index, follow';
        $meta['author'] = $siteName;
        
        return $meta;
    }

    /**
     * Générer Schema.org JSON-LD pour Organization
     */
    public static function generateOrganizationSchema()
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => 'CSAR',
            'legalName' => 'Commissariat à la Sécurité Alimentaire et à la Résilience',
            'url' => 'https://csar.sn',
            'logo' => asset('images/csar-logo.png'),
            'description' => 'Institution publique dédiée à la sécurité alimentaire et à la résilience au Sénégal',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'SN',
                'addressLocality' => 'Dakar',
                'addressRegion' => 'Dakar',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+221-XX-XXX-XX-XX',
                'contactType' => 'customer service',
                'email' => 'contact@csar.sn',
                'areaServed' => 'SN',
                'availableLanguage' => ['fr', 'en']
            ],
            'sameAs' => [
                'https://www.facebook.com/csar.sn',
                'https://twitter.com/csar_sn',
                // Ajouter autres réseaux sociaux
            ]
        ];
    }

    /**
     * Générer Schema.org pour un Article (Actualité)
     */
    public static function generateArticleSchema($article)
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'description' => Str::limit(strip_tags($article->content), 200),
            'image' => $article->featured_image ? asset('storage/' . $article->featured_image) : asset('images/csar-default.jpg'),
            'datePublished' => $article->published_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => 'CSAR',
                'url' => 'https://csar.sn'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'CSAR',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/csar-logo.png')
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url('/fr/actualites/' . $article->id)
            ]
        ];
    }

    /**
     * Générer Schema.org pour BreadcrumbList
     */
    public static function generateBreadcrumbSchema($breadcrumbs)
    {
        $items = [];
        $position = 1;

        foreach ($breadcrumbs as $breadcrumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url']
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }

    /**
     * Optimiser une image pour le web
     */
    public static function optimizeImageUrl($imagePath, $width = null, $height = null, $quality = 85)
    {
        // Si l'image n'existe pas, retourner l'URL par défaut
        if (!$imagePath) {
            return asset('images/default-placeholder.jpg');
        }

        // Pour l'instant, retourner l'URL normale
        // TODO: Intégrer service de redimensionnement (Imagick, Intervention Image)
        return asset('storage/' . $imagePath);
    }

    /**
     * Générer les meta tags alt-lang pour multilinguisme
     */
    public static function generateAlternateLinks($route, $params = [])
    {
        return [
            'fr' => route($route, array_merge(['locale' => 'fr'], $params)),
            'en' => route($route, array_merge(['locale' => 'en'], $params)),
        ];
    }

    /**
     * Générer un extrait optimisé pour SEO
     */
    public static function generateExcerpt($content, $length = 160)
    {
        // Retirer les balises HTML
        $text = strip_tags($content);
        
        // Retirer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Limiter la longueur
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length);
            // Couper au dernier mot complet
            $text = substr($text, 0, strrpos($text, ' ')) . '...';
        }
        
        return trim($text);
    }

    /**
     * Générer un slug SEO-friendly
     */
    public static function generateSlug($title)
    {
        return Str::slug($title, '-', 'fr');
    }

    /**
     * Générer des mots-clés depuis le contenu
     */
    public static function extractKeywords($content, $limit = 10)
    {
        // Retirer HTML
        $text = strip_tags($content);
        
        // Convertir en minuscules
        $text = mb_strtolower($text);
        
        // Mots courants à ignorer (stop words français)
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'mais', 'pour', 'dans', 'sur', 'avec', 'par', 'ce', 'ces', 'son', 'sa', 'ses', 'qui', 'que', 'dont', 'où'];
        
        // Extraire les mots
        preg_match_all('/\b[\w]{4,}\b/u', $text, $matches);
        $words = $matches[0];
        
        // Filtrer les stop words
        $words = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords);
        });
        
        // Compter les occurrences
        $wordCount = array_count_values($words);
        
        // Trier par fréquence
        arsort($wordCount);
        
        // Retourner les N premiers
        return array_slice(array_keys($wordCount), 0, $limit);
    }
}











































