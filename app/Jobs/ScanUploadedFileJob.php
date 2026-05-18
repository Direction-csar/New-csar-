<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Scanne un fichier uploadé via ClamAV (commande `clamdscan`).
 *
 * Prérequis serveur :
 *   sudo apt install clamav clamav-daemon
 *   sudo systemctl enable --now clamav-daemon
 *   sudo freshclam
 *
 * Si un virus est détecté, le fichier est supprimé et un log critique
 * est émis (idéalement remonté à un SIEM / Sentry).
 *
 * Audit OWASP A04 — Insecure Design (uploads).
 */
class ScanUploadedFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Disque Laravel (ex: 'public', 'local'). */
    public string $disk;

    /** Chemin relatif sur le disque. */
    public string $path;

    /** Modèle propriétaire (optionnel) — pour notifier l'admin. */
    public ?string $ownerModel;
    public ?int $ownerId;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(string $disk, string $path, ?string $ownerModel = null, ?int $ownerId = null)
    {
        $this->disk = $disk;
        $this->path = $path;
        $this->ownerModel = $ownerModel;
        $this->ownerId = $ownerId;
    }

    public function handle(): void
    {
        // Si ClamAV n'est pas installé, on log un warning et on sort proprement.
        if (!$this->clamAvAvailable()) {
            Log::warning('ClamAV non disponible — scan ignoré', ['path' => $this->path]);
            return;
        }

        $absolutePath = Storage::disk($this->disk)->path($this->path);

        if (!is_file($absolutePath)) {
            Log::warning('Fichier introuvable pour scan AV', ['path' => $absolutePath]);
            return;
        }

        // Exécuter clamdscan (plus rapide que clamscan)
        $output = [];
        $exitCode = 0;
        exec('clamdscan --no-summary --fdpass ' . escapeshellarg($absolutePath) . ' 2>&1', $output, $exitCode);

        // 0 = clean ; 1 = infecté ; 2 = erreur
        if ($exitCode === 1) {
            $this->handleInfected($absolutePath, implode("\n", $output));
            return;
        }

        if ($exitCode === 2) {
            Log::error('Erreur ClamAV', [
                'path' => $absolutePath,
                'output' => $output,
            ]);
            // Re-tenter via le mécanisme Laravel.
            $this->release($this->backoff);
            return;
        }

        Log::info('Fichier scanné — clean', [
            'path' => $this->path,
            'owner' => $this->ownerModel . '#' . $this->ownerId,
        ]);
    }

    private function clamAvAvailable(): bool
    {
        // Vérifie la présence de la commande clamdscan.
        $which = trim((string) @shell_exec('command -v clamdscan'));
        return $which !== '';
    }

    private function handleInfected(string $absolutePath, string $report): void
    {
        Log::critical('VIRUS DÉTECTÉ — fichier supprimé', [
            'path' => $absolutePath,
            'owner' => $this->ownerModel . '#' . $this->ownerId,
            'report' => $report,
        ]);

        // Suppression du fichier infecté.
        try {
            Storage::disk($this->disk)->delete($this->path);
        } catch (\Throwable $e) {
            Log::error('Échec suppression fichier infecté', ['error' => $e->getMessage()]);
        }

        // Suppression de l'entrée DB associée (best-effort, non bloquant).
        if ($this->ownerModel && $this->ownerId && class_exists($this->ownerModel)) {
            try {
                $this->ownerModel::query()->whereKey($this->ownerId)->delete();
            } catch (\Throwable $e) {
                Log::error('Échec suppression entrée DB associée au virus', ['error' => $e->getMessage()]);
            }
        }
    }
}
