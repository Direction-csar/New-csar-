<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TabaskiConfig extends Model
{
    protected $table = 'tabaski_config';
    protected $fillable = ['cle', 'valeur', 'label'];

    public static function get(string $cle, $default = null): ?string
    {
        return static::where('cle', $cle)->value('valeur') ?? $default;
    }

    public static function set(string $cle, string $valeur): void
    {
        static::where('cle', $cle)->update(['valeur' => $valeur]);
    }

    public static function estFerme(): bool
    {
        if (static::get('inscriptions_ouvertes', '1') === '0') {
            return true;
        }
        $expiry = static::get('date_expiration');
        return $expiry ? now()->gt(Carbon::parse($expiry)) : false;
    }

    public static function dateExpiration(): Carbon
    {
        return Carbon::parse(static::get('date_expiration', '2026-04-22 23:59:59'));
    }
}
