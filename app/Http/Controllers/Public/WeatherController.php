<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;

/**
 * API publique météo — proxy vers Open-Meteo (données réelles, aucune clé).
 * Toutes les entrées sont validées ; les coordonnées sont arrondies à 2
 * décimales côté cache, la position précise du visiteur n'est pas stockée.
 */
class WeatherController extends Controller
{
    public function __construct(private readonly WeatherService $weather)
    {
    }

    private function coords(Request $request): array
    {
        $v = $request->validate([
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
        ]);
        $lat = isset($v['lat']) ? (float) $v['lat'] : WeatherService::DAKAR['lat'];
        $lon = isset($v['lon']) ? (float) $v['lon'] : WeatherService::DAKAR['lon'];
        return [round($lat, 4), round($lon, 4)];
    }

    /** GET /api/weather?lat=&lon= — conditions + prévisions horaires et journalières. */
    public function show(Request $request)
    {
        [$lat, $lon] = $this->coords($request);
        $data = $this->weather->forecast($lat, $lon);

        if (!$data) {
            return response()->json(['error' => 'Données météo temporairement indisponibles'], 503)
                ->withHeaders(['Cache-Control' => 'no-cache']);
        }

        return response()->json($data)->withHeaders(['Cache-Control' => 'no-cache']);
    }

    /** GET /api/weather/forecast — conservé pour compatibilité (même source). */
    public function forecast(Request $request)
    {
        [$lat, $lon] = $this->coords($request);
        $data = $this->weather->forecast($lat, $lon);

        if (!$data) {
            return response()->json(['error' => 'Prévisions temporairement indisponibles'], 503)
                ->withHeaders(['Cache-Control' => 'no-cache']);
        }

        return response()->json([
            'city'     => $data['location']['name'],
            'country'  => $data['location']['country'],
            'forecast' => array_slice($data['daily'], 1, 5), // jours suivants
            'hourly'   => $data['hourly'],
        ])->withHeaders(['Cache-Control' => 'no-cache']);
    }

    /** GET /api/weather/search?q= — recherche de localités (Sénégal). */
    public function search(Request $request)
    {
        $v = $request->validate(['q' => 'required|string|min:2|max:80']);
        return response()->json(['results' => $this->weather->search($v['q'])])
            ->withHeaders(['Cache-Control' => 'no-cache']);
    }

    /** GET /api/weather/cities — liste des principales villes pour le sélecteur. */
    public function cities()
    {
        return response()->json(['cities' => WeatherService::CITIES])
            ->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }
}
