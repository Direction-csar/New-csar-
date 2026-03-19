<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Speech;
use App\Models\SimReport;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Générer le sitemap.xml dynamique
     */
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Pages statiques principales (priorité haute)
        $staticPages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/fr', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/en', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['url' => '/fr/a-propos', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => '/fr/institution', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => '/fr/missions', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => '/fr/actualites', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/fr/galerie', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => '/fr/rapports-sim', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/fr/discours', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/fr/partenaires', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/fr/projets', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/fr/ressources', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/fr/faq', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/fr/faire-un-don', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/fr/carte-interactive', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/fr/contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/fr/demande', 'priority' => '0.9', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>' . URL::to($page['url']) . '</loc>';
            $sitemap .= '<lastmod>' . Carbon::now()->toAtomString() . '</lastmod>';
            $sitemap .= '<changefreq>' . $page['changefreq'] . '</changefreq>';
            $sitemap .= '<priority>' . $page['priority'] . '</priority>';
            $sitemap .= '</url>';
        }

        // Actualités dynamiques (priorité moyenne-haute)
        try {
            $actualites = News::published()
                ->orderBy('published_at', 'desc')
                ->get();

            foreach ($actualites as $actu) {
                $sitemap .= '<url>';
                $sitemap .= '<loc>' . URL::to('/fr/actualites/' . $actu->id) . '</loc>';
                $sitemap .= '<lastmod>' . $actu->updated_at->toAtomString() . '</lastmod>';
                $sitemap .= '<changefreq>monthly</changefreq>';
                $sitemap .= '<priority>0.7</priority>';
                $sitemap .= '</url>';
            }
        } catch (\Exception $e) {
            // Table peut ne pas exister
        }

        // Discours officiels
        try {
            $discours = Speech::where('status', 'published')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($discours as $speech) {
                $sitemap .= '<url>';
                $sitemap .= '<loc>' . URL::to('/fr/discours/' . $speech->id) . '</loc>';
                $sitemap .= '<lastmod>' . $speech->updated_at->toAtomString() . '</lastmod>';
                $sitemap .= '<changefreq>yearly</changefreq>';
                $sitemap .= '<priority>0.6</priority>';
                $sitemap .= '</url>';
            }
        } catch (\Exception $e) {
            // Table peut ne pas exister
        }

        // Rapports SIM
        try {
            $simReports = SimReport::public()
                ->orderBy('published_at', 'desc')
                ->get();

            foreach ($simReports as $report) {
                $sitemap .= '<url>';
                $sitemap .= '<loc>' . URL::to('/fr/sim/' . $report->id) . '</loc>';
                $sitemap .= '<lastmod>' . $report->updated_at->toAtomString() . '</lastmod>';
                $sitemap .= '<changefreq>monthly</changefreq>';
                $sitemap .= '<priority>0.7</priority>';
                $sitemap .= '</url>';
            }
        } catch (\Exception $e) {
            // Table peut ne pas exister
        }

        // Pages légales (priorité basse mais importante pour SEO)
        $legalPages = [
            ['url' => '/fr/politique-confidentialite', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['url' => '/fr/conditions-utilisation', 'changefreq' => 'yearly', 'priority' => '0.5'],
            ['url' => '/fr/mentions-legales', 'changefreq' => 'yearly', 'priority' => '0.5'],
        ];

        foreach ($legalPages as $page) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>' . URL::to($page['url']) . '</loc>';
            $sitemap .= '<lastmod>' . Carbon::now()->toAtomString() . '</lastmod>';
            $sitemap .= '<changefreq>' . $page['changefreq'] . '</changefreq>';
            $sitemap .= '<priority>' . $page['priority'] . '</priority>';
            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Générer robots.txt
     */
    public function robots()
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /drh/\n";
        $robots .= "Disallow: /dg/\n";
        $robots .= "Disallow: /agent/\n";
        $robots .= "Disallow: /entrepot/\n";
        $robots .= "Disallow: /responsable/\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . URL::to('/sitemap.xml') . "\n";

        return response($robots, 200)
            ->header('Content-Type', 'text/plain');
    }
}











































