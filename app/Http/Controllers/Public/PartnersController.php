<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TechnicalPartner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PartnersController extends Controller
{
    public function index()
    {
        $partners = TechnicalPartner::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Partners coming from database (normalized to a simple array structure)
        $dbItems = $partners->map(function (TechnicalPartner $p) {
            return [
                'name' => $p->name,
                'organization' => $p->organization,
                'type' => $p->type ?: 'institution',
                'website' => $p->website,
                'logo_url' => $p->logo ? \Storage::url($p->logo) : null,
            ];
        })->values()->all();

        // Partenaires avec logos dans public/images/logos (liens officiels)
        $logosFolderItems = [
            [
                'name' => 'ARAA',
                'organization' => 'Agence Régionale pour l\'Agriculture et l\'Alimentation de la CEDEAO',
                'type' => 'agency',
                'website' => 'https://www.araa.org/fr',
                'logo_url' => asset('images/logos/logo arra.png'),
            ],
            [
                'name' => 'PAM',
                'organization' => 'Programme Alimentaire Mondial (World Food Programme)',
                'type' => 'agency',
                'website' => 'https://fr.wfp.org/',
                'logo_url' => asset('images/logos/logo pam.jpeg'),
            ],
            [
                'name' => 'Saudia',
                'organization' => 'Saudi Arabian Airlines',
                'type' => 'private',
                'website' => 'https://www.saudia.com/Pages/travel-with-saudia/where-we-fly/discover-saudi-arabia?rebranding_theme=Green&sc_lang=fr&sc_country=FR',
                'logo_url' => asset('images/logos/logo arabie saudia.png'),
            ],
        ];

        // Curated partners from the annual report (logos expected in public/images/partners)
        $staticItems = [
            // FSRP - Programme de Résilience du Système Alimentaire
            [
                'name' => 'FSRP',
                'organization' => 'Programme de Résilience du Système Alimentaire en Afrique de l\'Ouest',
                'type' => 'agency',
                'website' => 'https://fsrp.araa.org/fr',
                'logo_url' => asset('images/partners/fsrp.png')
            ],
            // JICA - Agence Japonaise de Coopération Internationale
            ['name' => 'JICA – Agence Japonaise de Coopération Internationale', 'type' => 'agency', 'website' => 'https://www.jica.go.jp/french/', 'logo_url' => asset('images/partners/jica.jpg')],
            // ANSD - Agence Nationale de la Statistique et de la Démographie
            ['name' => 'ANSD – Agence Nationale de la Statistique et de la Démographie', 'type' => 'institution', 'website' => 'https://recrute.ansd.sn/', 'logo_url' => asset('images/partners/ANSD.png')],
            // FONGIP - Fonds de Garantie des Investissements Prioritaires
            ['name' => 'FONGIP – Fonds de Garantie des Investissements Prioritaires', 'type' => 'institution', 'website' => 'https://www.fongip.sn/', 'logo_url' => asset('images/partners/fongip.jpeg')],
        ];

        // Map known slugs to direct websites and types
        $slugMap = [
            'fsrp' => ['url' => 'https://fsrp.araa.org/fr', 'type' => 'agency', 'name' => 'FSRP'],
            'jica' => ['url' => 'https://www.jica.go.jp/french/', 'type' => 'agency', 'name' => 'JICA'],
            'ansd' => ['url' => 'https://recrute.ansd.sn/', 'type' => 'institution', 'name' => 'ANSD'],
            'fongip' => ['url' => 'https://www.fongip.sn/', 'type' => 'institution', 'name' => 'FONGIP'],
        ];

        // Read all files from public/images/partners and build clickable items
        $fileItems = [];
        $files = File::exists(public_path('images/partners')) ? File::files(public_path('images/partners')) : [];
        foreach ($files as $file) {
            $basename = $file->getFilename();
            $slug = strtolower(pathinfo($basename, PATHINFO_FILENAME));
            $map = $slugMap[$slug] ?? null;
            $fileItems[] = [
                'name' => $map['name'] ?? Str::headline(str_replace(['_', '-'], ' ', $slug)),
                'organization' => null,
                'type' => $map['type'] ?? 'agency',
                'website' => $map['url'] ?? '#',
                'logo_url' => asset('images/partners/' . $basename),
            ];
        }

        // Partenaires : dossiers partners + logos (ARAA, PAM, Saudia)
        $allItems = collect($fileItems)
            ->merge($logosFolderItems)
            ->unique(fn ($item) => ($item['logo_url'] ?? '') . '|' . ($item['name'] ?? ''))
            ->values();
        $grouped = $allItems->groupBy('type');

        return view('public.partners', [
            'grouped' => $grouped,
        ]);
    }
}