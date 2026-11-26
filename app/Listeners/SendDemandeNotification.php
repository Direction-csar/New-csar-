<?php

namespace App\Listeners;

use App\Events\DemandeCreated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendDemandeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(DemandeCreated $event): void
    {
        try {
            $demande = $event->demande;
            
            // Créer la notification
            Notification::createNotification(
                'Nouvelle demande d\'aide',
                "Une nouvelle demande d'aide alimentaire a été soumise par {$demande->nom_complet}.",
                'demande',
                [
                    'demande_id' => $demande->id,
                    'nom' => $demande->nom_complet,
                    'type' => $demande->type_demande ?? 'Aide alimentaire',
                    'tracking_code' => $demande->tracking_code ?? null
                ],
                $demande,
                'file-text',
                route('admin.demandes.show', $demande->id)
            );

            Log::info('Notification créée pour nouvelle demande', [
                'demande_id' => $demande->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de notification pour demande', [
                'exception_type' => get_class($e),
                'demande_id' => $event->demande->id
            ]);
        }
    }
}
