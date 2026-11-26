<?php

namespace App\Jobs;

use App\Mail\RequestWorkflowUpdated;
use App\Models\PublicRequest;
use App\Services\RequestNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendRequestWorkflowNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public string $deliveryId)
    {
    }

    public function handle(): void
    {
        // Claim before calling the provider. Duplicate queue delivery cannot resend.
        $claimed = DB::table('request_notification_deliveries')->where('id', $this->deliveryId)
            ->whereIn('state', ['prepared', 'queued'])->update(['state' => 'sending', 'updated_at' => now()]);
        if (!$claimed) {
            return;
        }
        $delivery = DB::table('request_notification_deliveries')->find($this->deliveryId);
        $request = PublicRequest::find($delivery->public_request_id);
        try {
            $state = 'unavailable';
            $transport = config('mail.mailers.'.config('mail.default').'.transport');
            $usable = app()->environment('testing') || !in_array($transport, ['log', 'array'], true);
            if ($usable && $request && filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($request->email)->send(new RequestWorkflowUpdated($request->tracking_code, PublicRequest::publicStatusLabel($delivery->to_status)));
                // Transport accepted the message; inbox delivery is not known.
                $state = 'accepted';
            }
            DB::table('request_notification_deliveries')->where('id', $delivery->id)->update([
                'state' => $state, 'accepted_at' => $state === 'accepted' ? now() : null, 'updated_at' => now(),
            ]);
            if ($request) {
                match ($delivery->to_status) {
                    'document_attente' => RequestNotificationService::notifySignataires($request),
                    'signee' => RequestNotificationService::notifyScanneurs($request),
                    'scannee' => RequestNotificationService::notifyDgForApproval($request),
                    'approuvee' => RequestNotificationService::notifyDtl($request),
                    default => null,
                };
            }
        } catch (\Throwable) {
            // Provider timeouts are ambiguous: do not automatically resend.
            DB::table('request_notification_deliveries')->where('id', $delivery->id)
                ->where('state', 'sending')->update(['state' => 'failed_or_unknown', 'updated_at' => now()]);
            \Illuminate\Support\Facades\Log::warning('request_notification_failed', ['delivery_id' => $delivery->id]);
        }
    }
}
