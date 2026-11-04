<?php

namespace App\Jobs;

use App\Mail\BrevoTransportException;
use App\Mail\RequestTrackingCode;
use App\Models\PublicRequest;
use App\Services\RequestTrackingService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRequestTrackingCode implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public string $challengeId, public string $code)
    {
    }

    public function handle(): void
    {
        $challenge = DB::table('request_tracking_challenges')->find($this->challengeId);
        if (!$challenge || $challenge->consumed_at || now()->gte($challenge->expires_at)) {
            return;
        }
        if (!RequestTrackingService::mailChannelAvailable()) {
            $this->mark('unavailable', 'transport_inactive');
            Log::warning('request_tracking_mail_transport_unavailable');
            return;
        }
        $request = PublicRequest::find($challenge->public_request_id);
        if (!$request || !hash_equals($challenge->email_hash ?? '', RequestTrackingService::digest(strtolower(trim((string) $request->email))))) {
            // Aucun dossier ne correspond : rien n'est envoyé, jamais à une adresse saisie par le visiteur.
            $this->mark('skipped');
            return;
        }

        try {
            $sent = Mail::to($request->email)->send(new RequestTrackingCode($this->code));
        } catch (\Throwable $e) {
            $reason = $e instanceof BrevoTransportException
                ? "brevo:{$e->providerCode}:{$e->httpStatus}"
                : class_basename($e);
            // Nouvelle tentative unique pour les incidents passagers (réseau, 5xx, 429).
            $transient = $e instanceof BrevoTransportException
                ? $e->providerCode === 'connection_failed' || $e->httpStatus === 429 || $e->httpStatus >= 500
                : true;
            if ($transient && $this->attempts() < $this->tries) {
                $this->mark('queued', $reason);
                $this->release(20);
                return;
            }
            $this->mark('failed', $reason);
            Log::warning('request_tracking_delivery_failed', ['challenge' => $this->challengeId, 'reason' => $reason]);
            return;
        }

        DB::table('request_tracking_challenges')->where('id', $this->challengeId)->update([
            'delivery_status' => 'accepted',
            'delivery_error' => null,
            'provider_message_id' => $sent?->getMessageId() ? substr((string) $sent->getMessageId(), 0, 191) : null,
            'delivery_accepted_at' => now(),
            'updated_at' => now(),
        ]);
        Log::info('request_tracking_delivery_accepted', ['challenge' => $this->challengeId]);
    }

    private function mark(string $status, ?string $error = null): void
    {
        DB::table('request_tracking_challenges')->where('id', $this->challengeId)->update([
            'delivery_status' => $status,
            'delivery_error' => $error ? substr($error, 0, 120) : null,
            'updated_at' => now(),
        ]);
    }
}
