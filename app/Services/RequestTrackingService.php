<?php

namespace App\Services;

use App\Jobs\SendRequestTrackingCode;
use App\Models\PublicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class RequestTrackingService
{
    public const MESSAGE = 'Si l\'adresse correspond au dossier, un code de vérification va lui être envoyé. Vérifiez également vos courriers indésirables.';
    public const UNAVAILABLE = 'La vérification par e-mail est temporairement indisponible. Réessayez plus tard ou contactez le CSAR au +221 77 645 92 42 (contact@csar.sn).';

    public static function digest(string $value): string
    {
        return hash_hmac('sha256', $value, config('app.key'));
    }

    /** Retire espaces, espaces insécables et caractères invisibles d'une référence collée. */
    public static function normalizeReference(string $reference): string
    {
        return strtoupper((string) preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $reference));
    }

    /**
     * Le canal e-mail n'est réputé disponible que si un vrai transport est configuré
     * (jamais « log » ou « array ») et qu'une file asynchrone traite les envois.
     */
    public static function mailChannelAvailable(): bool
    {
        $transport = config('mail.mailers.'.config('mail.default').'.transport');
        // En test, « array » sert de transport capturable ; il reste inactif partout ailleurs.
        $inactive = app()->environment('testing') ? [null, 'log'] : [null, 'log', 'array'];
        return !in_array($transport, $inactive, true)
            && (app()->environment('testing') || config('request_tracking.queue_connection') !== 'sync');
    }

    /**
     * État présentable au visiteur, sans révéler si l'adresse correspond au dossier :
     * « skipped » (aucune correspondance) est rendu comme une remise réussie conditionnelle.
     */
    public function deliveryState(Request $http): ?string
    {
        $id = $http->session()->get('request_tracking_challenge');
        $challenge = $id ? DB::table('request_tracking_challenges')->where('id', $id)->first() : null;
        if (!$challenge || !hash_equals($challenge->session_hash, self::digest((string) $http->session()->get('request_tracking_binding')))) {
            return null;
        }
        return match ($challenge->delivery_status) {
            'accepted', 'skipped' => 'sent',
            'failed' => 'failed',
            'unavailable' => 'unavailable',
            default => now()->diffInSeconds($challenge->created_at, true) > 60 ? 'delayed' : 'pending',
        };
    }

    public function issue(Request $http, string $reference, string $email): string
    {
        if (!self::mailChannelAvailable()) {
            \Illuminate\Support\Facades\Log::warning('request_tracking_mail_channel_unavailable');
            return 'unavailable';
        }
        $reference = self::normalizeReference($reference);
        $key = 'request-otp:'.self::digest($reference);
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return 'throttled';
        }
        RateLimiter::hit($key, 3600);
        $email = strtolower(trim($email));
        $request = PublicRequest::where('tracking_code', $reference)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        $id = (string) Str::uuid();
        $code = (string) random_int(100000, 999999);
        if (!$http->session()->has('request_tracking_binding')) {
            $http->session()->put('request_tracking_binding', Str::random(64));
        }
        DB::table('request_tracking_challenges')->insert([
            'id' => $id,
            'public_request_id' => $request?->id,
            'email_hash' => $request ? self::digest($email) : null,
            'session_hash' => self::digest($http->session()->get('request_tracking_binding')),
            'code_hash' => self::digest($id.':'.$code),
            'expires_at' => now()->addMinutes(10),
            'delivery_status' => 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $http->session()->put('request_tracking_challenge', $id);
        // Queue a job even for absent dossiers; never send to a caller-supplied address.
        try {
            SendRequestTrackingCode::dispatch($id, $code)->onConnection(config('request_tracking.queue_connection'));
        } catch (\Throwable $e) {
            DB::table('request_tracking_challenges')->where('id', $id)
                ->update(['delivery_status' => 'failed', 'delivery_error' => 'queue:'.class_basename($e), 'updated_at' => now()]);
            \Illuminate\Support\Facades\Log::warning('request_tracking_queue_failed', ['reason' => class_basename($e)]);
        }
        return 'issued';
    }

    public function confirm(Request $http, string $code): ?PublicRequest
    {
        return DB::transaction(function () use ($http, $code) {
            $id = $http->session()->get('request_tracking_challenge');
            $challenge = DB::table('request_tracking_challenges')->where('id', $id)->lockForUpdate()->first();
            if (!$challenge || $challenge->consumed_at || $challenge->attempts >= 5
                || now()->gte($challenge->expires_at)
                || !hash_equals($challenge->session_hash, self::digest((string) $http->session()->get('request_tracking_binding')))) {
                return null;
            }
            if (!DB::table('request_tracking_challenges')->where('id', $id)
                ->where('attempts', $challenge->attempts)->whereNull('consumed_at')
                ->update(['attempts' => $challenge->attempts + 1, 'updated_at' => now()])) {
                return null;
            }
            $valid = hash_equals($challenge->code_hash, self::digest($id.':'.$code));
            $request = PublicRequest::find($challenge->public_request_id);
            if (!$valid || !$request || !hash_equals($challenge->email_hash ?? '', self::digest(strtolower(trim((string) $request->email))))) {
                return null;
            }
            DB::table('request_tracking_challenges')->where('id', $id)->update(['consumed_at' => now()]);
            $http->session()->forget('request_tracking_challenge');
            $http->session()->put('request_tracking_grant', [
                'id' => $request->id,
                'email_hash' => $challenge->email_hash,
                'expires' => now()->addMinutes(20)->timestamp,
            ]);
            return $request;
        });
    }

    public function authorized(Request $http, string $reference): ?PublicRequest
    {
        $grant = $http->session()->get('request_tracking_grant');
        if (!$grant || $grant['expires'] <= now()->timestamp) {
            return null;
        }
        $request = PublicRequest::whereKey($grant['id'])->where('tracking_code', $reference)->first();
        return $request && hash_equals($grant['email_hash'], self::digest(strtolower(trim((string) $request->email))))
            ? $request : null;
    }
}
