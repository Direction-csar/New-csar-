<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Règle de validation stricte pour les uploads de fichiers.
 *
 * - Vérifie le type MIME RÉEL (via finfo) — pas juste l'extension
 * - Vérifie la taille
 * - Bloque les exécutables et scripts
 *
 * Usage :
 *   $request->validate([
 *       'avatar' => ['required', 'file', new SecureFileUpload(['image/jpeg','image/png','image/webp'], 5_000_000)],
 *   ]);
 */
class SecureFileUpload implements ValidationRule
{
    /**
     * Extensions/MIME totalement interdits, peu importe le contexte.
     */
    protected const BLACKLIST_MIMES = [
        'application/x-php', 'application/x-httpd-php',
        'application/x-sh', 'application/x-csh',
        'application/x-msdownload', 'application/x-msi',
        'application/x-executable', 'application/x-elf',
        'application/x-bat', 'application/x-msdos-program',
        'application/javascript', 'text/javascript',
        'text/x-php', 'text/x-shellscript',
    ];

    protected const BLACKLIST_EXTS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar',
        'sh', 'bash', 'zsh', 'csh',
        'exe', 'msi', 'bat', 'cmd', 'com', 'scr', 'pif',
        'js', 'mjs', 'vbs', 'wsf', 'jar',
    ];

    public function __construct(
        protected array $allowedMimes,
        protected int $maxSizeBytes = 10_000_000
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('Le :attribute doit être un fichier valide.');
            return;
        }

        if (!$value->isValid()) {
            $fail('Le :attribute n\'a pas été correctement téléversé.');
            return;
        }

        // Taille
        if ($value->getSize() > $this->maxSizeBytes) {
            $maxMb = number_format($this->maxSizeBytes / 1_000_000, 1);
            $fail("Le :attribute dépasse la taille maximale de {$maxMb} Mo.");
            return;
        }

        // Extension blacklistée
        $ext = strtolower($value->getClientOriginalExtension());
        if (in_array($ext, self::BLACKLIST_EXTS, true)) {
            $fail('Ce type de fichier est interdit pour des raisons de sécurité.');
            return;
        }

        // MIME réel via finfo (pas l'header envoyé par le client)
        $realMime = mime_content_type($value->getRealPath()) ?: $value->getMimeType();

        if (in_array($realMime, self::BLACKLIST_MIMES, true)) {
            $fail('Le contenu du fichier est interdit pour des raisons de sécurité.');
            return;
        }

        if (!in_array($realMime, $this->allowedMimes, true)) {
            $allowed = implode(', ', $this->allowedMimes);
            $fail("Le type de fichier détecté ({$realMime}) n'est pas autorisé. Autorisés : {$allowed}.");
            return;
        }
    }
}
