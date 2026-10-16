<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service météo public CSAR.
 *
 * Données : Open-Meteo (gratuit, sans clé) — https://open-meteo.com
 * Géocodage inverse : Nominatim (OSM) avec fallback sur la table
 * des principales villes du Sénégal (distance la plus proche).
 * Les réponses sont mises en cache 10 min ; une copie "stale"
 * illimitée sert de dernier recours en cas d'indisponibilité.
 */
class WeatherService
{
    public const DAKAR = ['name' => 'Dakar', 'country' => 'Sénégal', 'lat' => 14.6928, 'lon' => -17.4467];

    /** Principales localités du Sénégal (capitales régionales + grandes villes). */
    public const CITIES = [
        ['name' => 'Dakar',       'lat' => 14.6928, 'lon' => -17.4467],
        ['name' => 'Pikine',      'lat' => 14.7545, 'lon' => -17.3986],
        ['name' => 'Thiès',       'lat' => 14.7886, 'lon' => -16.9260],
        ['name' => 'Saint-Louis', 'lat' => 16.0179, 'lon' => -16.4896],
        ['name' => 'Kaolack',     'lat' => 14.1519, 'lon' => -16.0726],
        ['name' => 'Ziguinchor',  'lat' => 12.5680, 'lon' => -16.2719],
        ['name' => 'Tambacounda', 'lat' => 13.7707, 'lon' => -13.6673],
        ['name' => 'Diourbel',    'lat' => 14.6550, 'lon' => -16.2336],
        ['name' => 'Louga',       'lat' => 15.6187, 'lon' => -16.2244],
        ['name' => 'Kolda',       'lat' => 12.8833, 'lon' => -14.9500],
        ['name' => 'Matam',       'lat' => 15.6559, 'lon' => -13.2554],
        ['name' => 'Fatick',      'lat' => 14.3390, 'lon' => -16.4110],
        ['name' => 'Kaffrine',    'lat' => 14.1059, 'lon' => -15.3850],
        ['name' => 'Kédougou',    'lat' => 12.5600, 'lon' => -12.1800],
        ['name' => 'Sédhiou',     'lat' => 12.7083, 'lon' => -15.5569],
        ['name' => 'Mbour',       'lat' => 14.4198, 'lon' => -16.9639],
        ['name' => 'Touba',       'lat' => 14.8667, 'lon' => -15.8833],
        ['name' => 'Richard-Toll','lat' => 16.4625, 'lon' => -15.7008],
    ];

    /**
     * Conditions actuelles + prévisions horaires (24 h) et journalières (7 j).
     * Retourne null si aucune donnée (même stale) n'est disponible.
     */
    public function forecast(float $lat, float $lon): ?array
    {
        $key    = 'wx_' . round($lat, 2) . '_' . round($lon, 2);
        $fresh  = Cache::get($key);
        if ($fresh) {
            return $fresh;
        }

        $data = $this->fetchOpenMeteo($lat, $lon);
        if (!$data || empty($data['current']) || empty($data['daily']['time'])) {
            // Dernier recours : données périmées si elles existent.
            $stale = Cache::get($key . '_stale');
            if ($stale) {
                $stale['stale'] = true;
                return $stale;
            }
            return null;
        }

        $payload = $this->transform($lat, $lon, $data);
        Cache::put($key, $payload, now()->addMinutes(10));
        Cache::put($key . '_stale', $payload, now()->addDays(7));

        return $payload;
    }

    private function fetchOpenMeteo(float $lat, float $lon): ?array
    {
        try {
            $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude'    => $lat,
                'longitude'   => $lon,
                'current'     => 'temperature_2m,relative_humidity_2m,apparent_temperature,is_day,weather_code,pressure_msl,wind_speed_10m,wind_direction_10m,precipitation',
                'hourly'      => 'temperature_2m,precipitation_probability,weather_code,is_day,visibility',
                'daily'       => 'weather_code,temperature_2m_max,temperature_2m_min,apparent_temperature_max,precipitation_probability_max,precipitation_sum,wind_speed_10m_max',
                'timezone'    => 'auto',
                'wind_speed_unit' => 'kmh',
                'forecast_days'   => 7,
            ]);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('Open-Meteo indisponible : ' . $e->getMessage());
            return null;
        }
    }

    private function transform(float $lat, float $lon, array $data): array
    {
        $cur = $data['current'];
        $tz  = $data['timezone'] ?? 'Africa/Dakar';

        $now = Carbon::parse($cur['time'] ?? null, $tz);
        if (!($now instanceof Carbon) || $now->year < 2000) {
            $now = Carbon::now($tz);
        }

        // Prévisions horaires : 8 créneaux à partir de l'heure courante.
        $hourly = [];
        $times  = $data['hourly']['time'] ?? [];
        foreach ($times as $i => $t) {
            $h = Carbon::parse($t, $tz);
            if ($h->lt($now->copy()->startOfHour()) || count($hourly) >= 8) {
                continue;
            }
            $wmo = $this->wmoMap((int) ($data['hourly']['weather_code'][$i] ?? 0), (int) ($data['hourly']['is_day'][$i] ?? 1));
            $hourly[] = [
                'hour'     => $h->format('H:i'),
                'temp'     => isset($data['hourly']['temperature_2m'][$i]) ? (int) round($data['hourly']['temperature_2m'][$i]) : null,
                'rain'     => (int) ($data['hourly']['precipitation_probability'][$i] ?? 0),
                'desc'     => $wmo['description'],
                'icon'     => $wmo['icon'],
                'fa'       => $wmo['fa'],
            ];
        }

        $daily = [];
        foreach ($data['daily']['time'] as $i => $day) {
            $wmo = $this->wmoMap((int) ($data['daily']['weather_code'][$i] ?? 0), 1);
            $daily[] = [
                'date'      => $day,
                'day_label' => ucfirst(Carbon::parse($day)->locale('fr')->dayName),
                'temp_max'  => isset($data['daily']['temperature_2m_max'][$i]) ? (int) round($data['daily']['temperature_2m_max'][$i]) : null,
                'temp_min'  => isset($data['daily']['temperature_2m_min'][$i]) ? (int) round($data['daily']['temperature_2m_min'][$i]) : null,
                'rain_prob' => (int) ($data['daily']['precipitation_probability_max'][$i] ?? 0),
                'rain_sum'  => round((float) ($data['daily']['precipitation_sum'][$i] ?? 0), 1),
                'wind'      => isset($data['daily']['wind_speed_10m_max'][$i]) ? (int) round($data['daily']['wind_speed_10m_max'][$i]) : null,
                'desc'      => $wmo['description'],
                'icon'      => $wmo['icon'],
                'fa'        => $wmo['fa'],
            ];
        }

        $wmoCur = $this->wmoMap((int) ($cur['weather_code'] ?? 0), (int) ($cur['is_day'] ?? 1));

        // Visibilité actuelle : reprise du créneau horaire courant si disponible.
        $visibility = null;
        $hTime = $now->format('Y-m-d\TH:00');
        $idx = array_search($hTime, $times, true);
        if ($idx !== false && isset($data['hourly']['visibility'][$idx])) {
            $visibility = round(((float) $data['hourly']['visibility'][$idx]) / 1000, 1);
        }

        return [
            'location' => $this->resolveLocation($lat, $lon),
            'tz'       => $tz,
            'current'  => [
                'temp'        => isset($cur['temperature_2m']) ? (int) round($cur['temperature_2m']) : null,
                'feels_like'  => isset($cur['apparent_temperature']) ? (int) round($cur['apparent_temperature']) : null,
                'humidity'    => isset($cur['relative_humidity_2m']) ? (int) $cur['relative_humidity_2m'] : null,
                'pressure'    => isset($cur['pressure_msl']) ? (int) round($cur['pressure_msl']) : null,
                'wind'        => isset($cur['wind_speed_10m']) ? (int) round($cur['wind_speed_10m']) : null,
                'wind_dir'    => $cur['wind_direction_10m'] ?? null,
                'wind_label'  => $this->windDirection((float) ($cur['wind_direction_10m'] ?? 0)),
                'precip'      => round((float) ($cur['precipitation'] ?? 0), 1),
                'visibility'  => $visibility,
                'is_day'      => (int) ($cur['is_day'] ?? 1),
                'desc'        => $wmoCur['description'],
                'icon'        => $wmoCur['icon'],
                'fa'          => $wmoCur['fa'],
            ],
            'hourly'      => $hourly,
            'daily'       => $daily,
            'risks'       => $this->risks($cur, $daily),
            'updated_at'  => $now->format('H:i'),
            'updated_tz'  => $tz,
            'stale'       => false,
        ];
    }

    /**
     * Nom de la localité la plus proche : Nominatim puis, en secours,
     * la ville sénégalaise la plus proche de notre liste.
     */
    public function resolveLocation(float $lat, float $lon): array
    {
        $cacheKey = 'geo_' . round($lat, 2) . '_' . round($lon, 2);
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($lat, $lon) {
            $name = null;
            try {
                $r = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'CSAR-Platform/1.0 (contact@csar.sn)'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'jsonv2', 'lat' => $lat, 'lon' => $lon,
                        'zoom' => 10, 'accept-language' => 'fr',
                    ]);
                if ($r->successful()) {
                    $a = $r->json()['address'] ?? [];
                    $name = $a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? $a['state'] ?? null;
                }
            } catch (\Throwable $e) {
                Log::info('Géocodage inverse indisponible : ' . $e->getMessage());
            }

            if (!$name) {
                $name = $this->nearestCity($lat, $lon)['name'] ?? null;
            }

            return [
                'name'    => $name ?: 'Votre position',
                'country' => 'Sénégal',
                'lat'     => round($lat, 2),
                'lon'     => round($lon, 2),
            ];
        });
    }

    public function nearestCity(float $lat, float $lon): ?array
    {
        $best = null;
        foreach (self::CITIES as $c) {
            $d = $this->haversine($lat, $lon, $c['lat'], $c['lon']);
            if ($best === null || $d < $best['distance']) {
                $best = $c + ['distance' => $d];
            }
        }
        return $best;
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Recherche de localité : liste locale d'abord, puis géocodage
     * Open-Meteo restreint au Sénégal.
     */
    public function search(string $q): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }

        $norm = fn (string $s) => mb_strtolower(strtr($s, [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c',
        ]));
        $local = collect(self::CITIES)
            ->filter(fn ($c) => str_contains($norm($c['name']), $norm($q)))
            ->map(fn ($c) => ['name' => $c['name'], 'lat' => $c['lat'], 'lon' => $c['lon'], 'admin' => 'Sénégal'])
            ->values()
            ->all();
        if ($local) {
            return $local;
        }

        $cacheKey = 'geo_search_' . md5($norm($q));
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($q) {
            try {
                $r = Http::timeout(6)->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name' => $q, 'count' => 8, 'language' => 'fr', 'format' => 'json',
                ]);
                if (!$r->successful()) return [];
                $out = [];
                foreach ($r->json()['results'] ?? [] as $g) {
                    if (($g['country_code'] ?? null) !== 'SN') continue;
                    $out[] = [
                        'name'  => $g['name'],
                        'lat'   => (float) $g['latitude'],
                        'lon'   => (float) $g['longitude'],
                        'admin' => trim(implode(', ', array_filter([$g['admin1'] ?? null, 'Sénégal']))),
                    ];
                }
                return $out;
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /** Codes WMO → description FR, icône OWM (optionnelle) et Font Awesome. */
    private function wmoMap(int $code, int $isDay): array
    {
        $map = [
            0  => ['Ciel dégagé',                '01', 'sun'],
            1  => ['Plutôt dégagé',              '02', 'cloud-sun'],
            2  => ['Partiellement nuageux',      '03', 'cloud-sun'],
            3  => ['Couvert',                    '04', 'cloud'],
            45 => ['Brouillard',                 '50', 'smog'],
            48 => ['Brouillard givrant',         '50', 'smog'],
            51 => ['Bruine légère',              '09', 'cloud-rain'],
            53 => ['Bruine modérée',             '09', 'cloud-rain'],
            55 => ['Bruine dense',               '09', 'cloud-rain'],
            56 => ['Bruine verglaçante légère',  '09', 'cloud-rain'],
            57 => ['Bruine verglaçante dense',   '09', 'cloud-rain'],
            61 => ['Pluie faible',               '10', 'cloud-rain'],
            63 => ['Pluie modérée',              '10', 'cloud-rain'],
            65 => ['Pluie forte',                '10', 'cloud-showers-heavy'],
            66 => ['Pluie verglaçante légère',   '13', 'cloud-showers-heavy'],
            67 => ['Pluie verglaçante forte',    '13', 'cloud-showers-heavy'],
            71 => ['Neige faible',               '13', 'snowflake'],
            73 => ['Neige modérée',              '13', 'snowflake'],
            75 => ['Neige forte',                '13', 'snowflake'],
            77 => ['Grains de neige',            '13', 'snowflake'],
            80 => ['Averses faibles',            '09', 'cloud-sun-rain'],
            81 => ['Averses modérées',           '09', 'cloud-showers-heavy'],
            82 => ['Averses violentes',          '09', 'cloud-showers-heavy'],
            85 => ['Averses de neige faibles',   '13', 'snowflake'],
            86 => ['Averses de neige fortes',    '13', 'snowflake'],
            95 => ['Orage',                      '11', 'bolt'],
            96 => ['Orage avec grêle légère',    '11', 'bolt'],
            99 => ['Orage avec grêle forte',     '11', 'bolt'],
        ];
        $e = $map[$code] ?? ['Conditions inconnues', '01', 'cloud'];
        $suffix = in_array($e[1], ['01', '02', '03', '04'], true) ? 'd' : ($isDay ? 'd' : 'n');
        $fa = $e[2];
        if (!$isDay && $fa === 'sun') $fa = 'moon';
        if (!$isDay && $fa === 'cloud-sun') $fa = 'cloud-moon';
        return ['description' => $e[0], 'icon' => $e[1] . $suffix, 'fa' => 'fa-' . $fa];
    }

    private function windDirection(float $deg): string
    {
        $dirs = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSO', 'SO', 'OSO', 'O', 'ONO', 'NO', 'NNO'];
        return $dirs[(int) round($deg / 22.5) % 16];
    }

    /**
     * Signaux indicatifs calculés à partir des prévisions du modèle.
     * Ce ne sont PAS des alertes officielles (ANACIM).
     */
    private function risks(array $cur, array $daily): array
    {
        $risks = [];
        $next3 = array_slice($daily, 0, 3);

        $rainSum  = array_sum(array_column($next3, 'rain_sum'));
        $rainMax  = max(array_column($next3, 'rain_prob') ?: [0]);
        $heatMax  = max(array_column($next3, 'temp_max') ?: [0]);
        $windMax  = max(array_column($next3, 'wind') ?: [0]);

        if ($rainSum >= 30 || $rainMax >= 80) {
            $risks[] = ['level' => 'high', 'title' => 'Fortes précipitations possibles',
                'desc' => "Cumul prévu ~{$rainSum} mm sur 3 jours (probabilité max {$rainMax} %). Risque d'inondation localisée et difficultés d'accès aux marchés / zones de production."];
        } elseif ($rainMax >= 60) {
            $risks[] = ['level' => 'medium', 'title' => 'Pluies probables',
                'desc' => "Probabilité de pluie jusqu'à {$rainMax} % sous 3 jours. À suivre pour les activités agricoles et le transport des denrées."];
        }
        if ($heatMax >= 38) {
            $risks[] = ['level' => 'high', 'title' => 'Chaleur importante',
                'desc' => "Températures prévues jusqu'à {$heatMax} °C. Vigilance : hydratation des populations vulnérables, stockage des denrées sensibles."];
        } elseif ($heatMax >= 35) {
            $risks[] = ['level' => 'medium', 'title' => 'Fortes chaleurs',
                'desc' => "Maximales prévues jusqu'à {$heatMax} °C."];
        }
        if ($windMax >= 40) {
            $risks[] = ['level' => 'medium', 'title' => 'Vents forts',
                'desc' => "Rafales / vents prévus jusqu'à {$windMax} km/h : poussières, dégâts possibles sur cultures et structures légères."];
        }
        if (($cur['precipitation'] ?? 0) >= 5) {
            $risks[] = ['level' => 'medium', 'title' => 'Pluie en cours',
                'desc' => "Précipitations relevées : {$cur['precipitation']} mm sur la dernière heure."];
        }

        return $risks;
    }
}
