<?php

namespace App\Services;

use App\Models\PublicRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RequestNotificationService
{
    /**
     * Envoyer une notification au demandeur quand le workflow avance
     */
    public static function prepare(PublicRequest $request, string $eventId, string $oldStatus, string $newStatus): void
    {
        \Illuminate\Support\Facades\DB::table('request_notification_deliveries')->insertOrIgnore([
            'id' => $eventId, 'public_request_id' => $request->id,
            'from_status' => $oldStatus, 'to_status' => $newStatus,
            'state' => 'prepared', 'created_at' => now(), 'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::afterCommit(fn () => self::dispatchPrepared($eventId));
    }

    /**
     * Envoyer un SMS de notification (si service SMS configuré)
     */
    public static function dispatchPrepared(string $eventId): void
    {
        $query = \Illuminate\Support\Facades\DB::table('request_notification_deliveries')->where('id', $eventId);
        if (!(clone $query)->where('state', 'prepared')->update(['state' => 'queued', 'updated_at' => now()])) {
            return;
        }
        try {
            \App\Jobs\SendRequestWorkflowNotification::dispatch($eventId);
        } catch (\Throwable) {
            (clone $query)->where('state', 'queued')->update(['state' => 'prepared', 'updated_at' => now()]);
            Log::warning('request_notification_queue_failed', ['delivery_id' => $eventId]);
        }
    }

    /**
     * Notifier les signataires quand une demande est prête à être signée
     */
    public static function notifySignataires(PublicRequest $request): void
    {
        try {
            $signataires = \App\Models\User::whereIn('role', ['signataire', 'admin', 'super_admin'])->get();

            foreach ($signataires as $user) {
                if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::send('emails.request-role-pending', [
                        'request' => $request,
                        'userName' => $user->name,
                        'role' => 'signataire',
                        'actionLabel' => 'signer',
                        'actionUrl' => route('admin.demandes.edit', $request->id),
                        'dashboardUrl' => route('admin.demandes.dg-dashboard'),
                    ], function ($message) use ($user, $request) {
                        $message->to($user->email, $user->name)
                            ->subject('Signature requise - Demande ' . $request->tracking_code);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification signataires', ['exception_type' => get_class($e)]);
        }
    }

    /**
     * Notifier les scanneurs quand une demande est prête à être scannée
     */
    public static function notifyScanneurs(PublicRequest $request): void
    {
        try {
            $scanneurs = \App\Models\User::whereIn('role', ['scanneur', 'admin', 'super_admin'])->get();

            foreach ($scanneurs as $user) {
                if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::send('emails.request-role-pending', [
                        'request' => $request,
                        'userName' => $user->name,
                        'role' => 'scanneur',
                        'actionLabel' => 'scanner',
                        'actionUrl' => route('admin.demandes.edit', $request->id),
                        'dashboardUrl' => route('admin.demandes.dg-dashboard'),
                    ], function ($message) use ($user, $request) {
                        $message->to($user->email, $user->name)
                            ->subject('Scan requis - Demande ' . $request->tracking_code);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification scanneurs', ['exception_type' => get_class($e)]);
        }
    }

    /**
     * Notifier le DG quand une demande attend sa validation
     */
    public static function notifyDgForApproval(PublicRequest $request): void
    {
        try {
            $dgUsers = \App\Models\User::whereIn('role', ['dg', 'directeur_general', 'admin', 'super_admin'])->get();

            foreach ($dgUsers as $dg) {
                if (filter_var($dg->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::send('emails.request-dg-pending', [
                        'request' => $request,
                        'dgName' => $dg->name,
                        'approvalUrl' => route('dg.demandes.show', $request->id),
                    ], function ($message) use ($dg, $request) {
                        $message->to($dg->email, $dg->name)
                            ->subject('Validation DG requise - Demande ' . $request->tracking_code);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification DG', ['exception_type' => get_class($e)]);
        }
    }

    /**
     * Notifier le DTL quand un dossier approuvé lui est transmis
     */
    public static function notifyDtl(PublicRequest $request): void
    {
        try {
            $dtlUsers = \App\Models\User::whereIn('role', ['dtl'])->get();

            foreach ($dtlUsers as $dtl) {
                // Notification interne persistante
                try {
                    \App\Models\Notification::create([
                        'type' => 'demande_transmise_dtl',
                        'title' => 'Dossier approuvé à instruire',
                        'message' => "La demande {$request->tracking_code} a été validée par la Directrice et transmise au DTL.",
                        'user_id' => $dtl->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Notification interne DTL échouée', ['exception_type' => get_class($e)]);
                }

                if (filter_var($dtl->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Mail::raw(
                            "Bonjour,\n\nLe dossier {$request->tracking_code} ({$request->full_name}) a été approuvé et signé par la Direction Générale. "
                            . "Il vous est transmis pour instruction opérationnelle (missions inspecteur / sorties magasin).\n\n"
                            . "Espace DTL : " . route('dtl.dashboard') . "\n\nCSAR",
                            function ($message) use ($dtl, $request) {
                                $message->to($dtl->email, $dtl->name)
                                    ->subject('Dossier approuvé à instruire - ' . $request->tracking_code);
                            }
                        );
                    } catch (\Exception $e) {
                        Log::warning('Email DTL échoué', ['exception_type' => get_class($e)]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification DTL', ['exception_type' => get_class($e)]);
        }
    }

    /**
     * Envoyer un résumé quotidien des demandes en attente à l'admin
     */
    public static function sendDailyPendingSummary(): void
    {
        try {
            $pending = PublicRequest::whereIn('workflow_status', ['soumise', 'en_revue', 'document_attente'])
                ->orderBy('created_at', 'asc')
                ->limit(20)
                ->get();

            if ($pending->isEmpty()) {
                return;
            }

            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();

            foreach ($admins as $admin) {
                if (filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::send('emails.daily-pending-summary', [
                        'pending' => $pending,
                        'count' => $pending->count(),
                        'adminName' => $admin->name,
                    ], function ($message) use ($admin) {
                        $message->to($admin->email, $admin->name)
                            ->subject('Résumé quotidien - Demandes en attente CSAR');
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur résumé quotidien', ['exception_type' => get_class($e)]);
        }
    }
}
