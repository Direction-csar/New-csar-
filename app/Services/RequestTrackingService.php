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
    public const MESSAGE = 'Si les informations correspondent à un dossier, un code sera envoyé à son adresse email. Vérifiez également vos courriers indésirables.';

    public static function digest(string $value): string
    {
        return hash_hmac('sha256', $value, config('app.key'));
    }

    public function issue(Request $http, string $reference, string $email): void
    {
        if (!app()->environment('testing') && config('request_tracking.queue_connection') === 'sync') {
            \Illuminate\Support\Facades\Log::warning('request_tracking_requires_async_queue');
            return;
        }
        $reference = strtoupper(trim($reference));
        $key = 'request-otp:'.self::digest($reference);
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return;
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $http->session()->put('request_tracking_challenge', $id);
        // Queue a job even for absent dossiers; never send to a caller-supplied address.
        try {
            SendRequestTrackingCode::dispatch($id, $code)->onConnection(config('request_tracking.queue_connection'));
        } catch (\Throwable) {
            \Illuminate\Support\Facades\Log::warning('request_tracking_queue_failed');
        }
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
