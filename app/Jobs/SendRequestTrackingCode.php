<?php

namespace App\Jobs;

use App\Mail\RequestTrackingCode;
use App\Models\PublicRequest;
use App\Services\RequestTrackingService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendRequestTrackingCode implements ShouldQueue, ShouldBeEncrypted
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $challengeId, public string $code)
    {
    }

    public function handle(): void
    {
        $transport = config('mail.mailers.'.config('mail.default').'.transport');
        if (!app()->environment('testing') && in_array($transport, ['log', 'array'], true)) {
            \Illuminate\Support\Facades\Log::warning('request_tracking_mail_transport_unavailable');
            return;
        }
        $challenge = DB::table('request_tracking_challenges')->find($this->challengeId);
        if (!$challenge || $challenge->consumed_at || now()->gte($challenge->expires_at)) {
            return;
        }
        $request = PublicRequest::find($challenge->public_request_id);
        if (!$request || !hash_equals($challenge->email_hash ?? '', RequestTrackingService::digest(strtolower(trim((string) $request->email))))) {
            return;
        }
        try {
            Mail::to($request->email)->send(new RequestTrackingCode($this->code));
        } catch (\Throwable) {
            \Illuminate\Support\Facades\Log::warning('request_tracking_delivery_failed');
        }
    }
}
