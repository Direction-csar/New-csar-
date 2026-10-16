<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    private function fakeOpenMeteo(): array
    {
        $today = now()->format('Y-m-d');
        $hours = [];
        $times = [];
        $temps = [];
        $rain  = [];
        $codes = [];
        $day   = [];
        $vis   = [];
        for ($i = 0; $i < 48; $i++) {
            $times[] = now()->startOfHour()->addHours($i)->format('Y-m-d\TH:i');
            $temps[] = 30 + ($i % 5);
            $rain[]  = 20;
            $codes[] = 2;
            $day[]   = 1;
            $vis[]   = 15000;
        }
        return [
            'timezone' => 'Africa/Dakar',
            'current' => [
                'time' => now()->format('Y-m-d\TH:i'),
                'temperature_2m' => 31.4,
                'relative_humidity_2m' => 62,
                'apparent_temperature' => 33.9,
                'is_day' => 1,
                'weather_code' => 2,
                'pressure_msl' => 1012.3,
                'wind_speed_10m' => 18.5,
                'wind_direction_10m' => 250,
                'precipitation' => 0.0,
            ],
            'hourly' => [
                'time' => $times,
                'temperature_2m' => $temps,
                'precipitation_probability' => $rain,
                'weather_code' => $codes,
                'is_day' => $day,
                'visibility' => $vis,
            ],
            'daily' => [
                'time' => [$today, now()->addDay()->format('Y-m-d')],
                'weather_code' => [2, 61],
                'temperature_2m_max' => [34.0, 33.0],
                'temperature_2m_min' => [24.0, 23.0],
                'precipitation_probability_max' => [10, 70],
                'precipitation_sum' => [0.0, 12.5],
                'wind_speed_10m_max' => [20.0, 45.0],
            ],
        ];
    }

    private function fakeAll(): void
    {
        Http::fake([
            'api.open-meteo.com/v1/forecast*'        => Http::response($this->fakeOpenMeteo(), 200),
            'nominatim.openstreetmap.org/reverse*'   => Http::response(['address' => ['city' => 'Thiès', 'country' => 'Sénégal']], 200),
            'geocoding-api.open-meteo.com/*'         => Http::response(['results' => [
                ['name' => 'Tivaouane', 'latitude' => 14.95, 'longitude' => -16.82, 'country_code' => 'SN', 'admin1' => 'Thiès'],
            ]], 200),
        ]);
    }

    public function test_weather_returns_real_payload(): void
    {
        $this->fakeAll();
        $res = $this->getJson('/api/weather?lat=14.79&lon=-16.93');
        $res->assertOk()
            ->assertJsonPath('current.temp', 31)
            ->assertJsonPath('current.humidity', 62)
            ->assertJsonStructure(['location' => ['name'], 'current' => ['temp','feels_like','humidity','pressure','wind','desc'], 'hourly', 'daily', 'risks', 'updated_at']);
        $this->assertSame('Thiès', $res->json('location.name'));
        $this->assertCount(8, $res->json('hourly'));
        $this->assertCount(2, $res->json('daily'));
        // Vent fort 45 km/h → risque indicatif
        $this->assertNotEmpty($res->json('risks'));
    }

    public function test_weather_defaults_to_dakar(): void
    {
        $this->fakeAll();
        $res = $this->getJson('/api/weather');
        $res->assertOk();
        Http::assertSent(fn ($r) => str_contains($r->url(), 'latitude=14.6928'));
    }

    public function test_weather_validates_coords(): void
    {
        $this->getJson('/api/weather?lat=999')->assertStatus(422);
        $this->getJson('/api/weather?lon=abc')->assertStatus(422);
    }

    public function test_weather_503_when_provider_down(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 500)]);
        $this->getJson('/api/weather?lat=12.5&lon=-15.5')->assertStatus(503);
    }

    public function test_search_local_and_remote(): void
    {
        $this->fakeAll();
        $this->getJson('/api/weather/search?q=ked')
            ->assertOk()->assertJsonPath('results.0.name', 'Kédougou');
        $res = $this->getJson('/api/weather/search?q=tivaouane');
        $res->assertOk()->assertJsonPath('results.0.name', 'Tivaouane');
    }

    public function test_forecast_compat_endpoint(): void
    {
        $this->fakeAll();
        $this->getJson('/api/weather/forecast?lat=14.69&lon=-17.44')
            ->assertOk()->assertJsonStructure(['city', 'forecast']);
    }
}
