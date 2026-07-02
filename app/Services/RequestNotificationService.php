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
    public static function notifyWorkflowUpdate(PublicRequest $request, string $oldStatus, string $newStatus): void
    {
        try {
            $statusLabels = [
                'soumise' => 'soumise',
                'en_revue' => 'en cours de revue',
                'document_attente' => 'en attente de documents',
                'signee' => 'signée',
                'scannee' => 'scannée',
                'validee_dg' => 'validée par la Direction Générale',
                'approuvee' => 'approuvée',
                'rejetee' => 'rejetée',
                'cloturee' => 'clôturée',
            ];

            $label = $statusLabels[$newStatus] ?? $newStatus;
            $subject = 'Mise à jour de votre demande CSAR - ' . strtoupper($request->tracking_code);

            // Email
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                Mail::send('emails.request-workflow-update', [
                    'request' => $request,
                    'oldStatus' => $oldStatus,
                    'newStatus' => $newStatus,
                    'label' => $label,
                    'trackingUrl' => route('track.request.form', ['locale' => app()->getLocale()]),
                ], function ($message) use ($request, $subject) {
                    $message->to($request->email, $request->full_name)
                        ->subject($subject);
                });
            }

            // SMS si configuré
            self::sendSmsNotification($request, $label);

        } catch (\Exception $e) {
            Log::error('Erreur notification workflow demande: ' . $e->getMessage(), [
                'request_id' => $request->id,
                'tracking_code' => $request->tracking_code,
            ]);
        }
    }

    /**
     * Envoyer un SMS de notification (si service SMS configuré)
     */
    private static function sendSmsNotification(PublicRequest $request, string $statusLabel): void
    {
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (empty($phone) || strlen($phone) < 9) {
            return;
        }

        try {
            $message = "CSAR: Votre demande {$request->tracking_code} est maintenant {$statusLabel}. "
                     . "Suivez son avancement sur: " . route('track.request.form', ['locale' => app()->getLocale()]);

            // Si un service SMS est configuré, l'utiliser ici
            // Exemple avec Twilio ou autre:
            // self::sendViaTwilio($phone, $message);

            // Pour l'instant, on log juste
            Log::info('SMS notification préparé', [
                'phone' => $phone,
                'message' => $message,
                'tracking_code' => $request->tracking_code,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur SMS workflow: ' . $e->getMessage());
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
            Log::error('Erreur notification signataires: ' . $e->getMessage());
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
            Log::error('Erreur notification scanneurs: ' . $e->getMessage());
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
                        'approvalUrl' => route('admin.demandes.edit', $request->id),
                    ], function ($message) use ($dg, $request) {
                        $message->to($dg->email, $dg->name)
                            ->subject('Validation DG requise - Demande ' . $request->tracking_code);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification DG: ' . $e->getMessage());
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
            Log::error('Erreur résumé quotidien: ' . $e->getMessage());
        }
    }
}
