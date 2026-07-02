<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Planification du digest hebdomadaire (tous les lundis à 8h)
Artisan::command('schedule:weekly-digest', function () {
    if (now()->dayOfWeek === 1) { // Lundi
        Artisan::call('notifications:weekly-digest');
        $this->info('📊 Digest hebdomadaire planifié pour envoi.');
    }
})->purpose('Planifier l\'envoi du digest hebdomadaire');

// Nettoyage automatique des anciens logs d'audit (tous les dimanche)
Artisan::command('schedule:clean-audit', function () {
    if (now()->dayOfWeek === 0) { // Dimanche
        Artisan::call('audit:clean', ['--days' => 90, '--force' => true]);
        $this->info('🧹 Nettoyage automatique des logs d\'audit effectué.');
    }
})->purpose('Nettoyer automatiquement les anciens logs d\'audit');

// Planification des rapports SIM (tous les jours à 18h)
Artisan::command('schedule:sim-reports', function () {
    Artisan::call('sim:schedule');
    $this->info('📊 Rapports SIM planifiés générés.');
})->purpose('Générer automatiquement les rapports SIM selon la planification');

// Résumé quotidien des demandes en attente (tous les jours à 8h)
Artisan::command('schedule:daily-requests-summary', function () {
    if (now()->hour === 8) {
        Artisan::call('requests:send-daily-summary');
        $this->info('📧 Résumé quotidien des demandes envoyé.');
    }
})->purpose('Envoyer le résumé quotidien des demandes en attente aux administrateurs');
