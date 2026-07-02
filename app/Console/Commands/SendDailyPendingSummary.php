<?php

namespace App\Console\Commands;

use App\Services\RequestNotificationService;
use Illuminate\Console\Command;

class SendDailyPendingSummary extends Command
{
    protected $signature = 'requests:send-daily-summary';
    protected $description = 'Envoyer le résumé quotidien des demandes en attente aux administrateurs';

    public function handle(): int
    {
        $this->info('Envoi du résumé quotidien...');
        try {
            RequestNotificationService::sendDailyPendingSummary();
            $this->info('Résumé envoyé avec succès.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur: ' . $e->getMessage());
            return 1;
        }
    }
}
