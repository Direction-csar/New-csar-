<?php

namespace App\Services;

use App\Models\PublicRequest;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityService
{
    /**
     * Vérifier les doublons de demandes
     */
    public static function checkDuplicateRequest($email, $type, $description, $hours = 24)
    {
        try {
            $hash = self::generateDuplicateHash($email, $type, $description);

            $recentRequest = PublicRequest::where('duplicate_hash', $hash)
                ->where('created_at', '>=', Carbon::now()->subHours($hours))
                ->first();

            if ($recentRequest) {
                // Log de la tentative de doublon
                self::logAudit('duplicate_request_attempt', 'PublicRequest', $recentRequest->id, [
                    'email' => $email,
                    'type' => $type,
                    'duplicate_hash' => $hash,
                    'original_request_id' => $recentRequest->id
                ]);
            }

            return $recentRequest !== null;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des doublons de demande', [
                'error' => $e->getMessage(),
                'email' => $email,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Vérifier les doublons de messages de contact
     */
    public static function checkDuplicateContact($email, $subject, $message, $hours = 24)
    {
        try {
            $hash = self::generateDuplicateHash($email, $subject, $message);

            $recentContact = ContactMessage::where('duplicate_hash', $hash)
                ->where('created_at', '>=', Carbon::now()->subHours($hours))
                ->first();

            if ($recentContact) {
                // Log de la tentative de doublon
                self::logAudit('duplicate_contact_attempt', 'ContactMessage', $recentContact->id, [
                    'email' => $email,
                    'subject' => $subject,
                    'duplicate_hash' => $hash,
                    'original_contact_id' => $recentContact->id
                ]);
            }

            return $recentContact !== null;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des doublons de contact', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return false;
        }
    }

    /**
     * Vérifier les doublons d'inscription newsletter
     */
    public static function checkDuplicateNewsletter($email, $hours = 1)
    {
        try {
            $recentSubscription = NewsletterSubscriber::where('email', $email)
                ->where('created_at', '>=', Carbon::now()->subHours($hours))
                ->first();

            if ($recentSubscription) {
                // Log de la tentative de doublon
                self::logAudit('duplicate_newsletter_attempt', 'NewsletterSubscriber', $recentSubscription->id, [
                    'email' => $email,
                    'duplicate_hash' => self::generateDuplicateHash($email),
                    'original_subscriber_id' => $recentSubscription->id
                ]);
            }

            return $recentSubscription !== null;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des doublons newsletter', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return false;
        }
    }

    /**
     * Générer un hash pour détecter les doublons
     */
    public static function generateDuplicateHash(...$fields)
    {
        $combined = implode('|', array_map('strtolower', $fields));
        return hash('sha256', $combined);
    }

    /**
     * Enregistrer une action dans le journal d'audit
     */
    public static function logAudit($action, $modelType, $modelId = null, $data = [], $userId = null)
    {
        try {
            AuditLog::create([
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'user_id' => $userId ?? auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => $data,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'audit', [
                'error' => $e->getMessage(),
                'action' => $action,
                'model_type' => $modelType
            ]);
        }
    }

    /**
     * Vérifier la sécurité d'un mot de passe
     */
    public static function validatePasswordStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial';
        }

        return $errors;
    }

    /**
     * Générer un code de suivi unique
     */
    public static function generateTrackingCode($prefix = 'CSAR')
    {
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(bin2hex(random_bytes(4)));
        return $prefix . '-' . $timestamp . '-' . $random;
    }

    /**
     * Valider un numéro de téléphone sénégalais
     */
    public static function validateSenegalPhone($phone)
    {
        // Nettoyer le numéro
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

        // Formats acceptés : +221XXXXXXXXX, 221XXXXXXXXX, 0XXXXXXXXX
        $patterns = [
            '/^\+221[0-9]{9}$/',  // +221XXXXXXXXX
            '/^221[0-9]{9}$/',    // 221XXXXXXXXX
            '/^0[0-9]{9}$/'       // 0XXXXXXXXX
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nettoyer un numéro de téléphone pour le stockage
     */
    public static function cleanPhoneNumber($phone)
    {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

        // Convertir en format international
        if (preg_match('/^0([0-9]{9})$/', $cleanPhone, $matches)) {
            return '+221' . $matches[1];
        }

        if (preg_match('/^221([0-9]{9})$/', $cleanPhone, $matches)) {
            return '+221' . $matches[1];
        }

        if (preg_match('/^\+221([0-9]{9})$/', $cleanPhone, $matches)) {
            return '+221' . $matches[1];
        }

        return null;
    }

    /**
     * Vérifier la fréquence des requêtes (rate limiting)
     */
    public static function checkRateLimit($identifier, $maxRequests = 10, $windowMinutes = 60)
    {
        $key = 'rate_limit_' . md5($identifier);
        $requests = cache()->get($key, []);

        // Nettoyer les anciennes requêtes
        $cutoff = now()->subMinutes($windowMinutes);
        $requests = array_filter($requests, function($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        });

        if (count($requests) >= $maxRequests) {
            return false;
        }

        // Ajouter la nouvelle requête
        $requests[] = now();
        cache()->put($key, $requests, $windowMinutes * 60);

        return true;
    }

    /**
     * Sanitiser les données d'entrée
     */
    public static function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }

        if (is_string($input)) {
            // Supprimer les balises HTML dangereuses
            $input = strip_tags($input, '<p><br><strong><em><ul><ol><li>');

            // Échapper les caractères spéciaux
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

            // Nettoyer les espaces multiples
            $input = preg_replace('/\s+/', ' ', trim($input));
        }

        return $input;
    }

    /**
     * Vérifier les tentatives de connexion suspectes
     */
    public static function checkSuspiciousActivity($ip, $email = null)
    {
        $key = 'failed_attempts_' . md5($ip . ($email ?? ''));
        $attempts = cache()->get($key, 0);

        return $attempts >= 5; // Bloquer après 5 tentatives
    }

    /**
     * Incrémenter les tentatives échouées
     */
    public static function incrementFailedAttempts($ip, $email = null)
    {
        $key = 'failed_attempts_' . md5($ip . ($email ?? ''));
        $attempts = cache()->get($key, 0);
        cache()->put($key, $attempts + 1, 900); // 15 minutes
    }

    /**
     * Réinitialiser les tentatives échouées
     */
    public static function resetFailedAttempts($ip, $email = null)
    {
        $key = 'failed_attempts_' . md5($ip . ($email ?? ''));
        cache()->forget($key);
    }

    /**
     * Journaliser les connexions
     */
    public static function logLogin($user, $ip, $userAgent, $success)
    {
        try {
            \App\Models\AuditLog::create([
                'action' => $success ? 'login_success' : 'login_failed',
                'model_type' => 'User',
                'model_id' => $user->id,
                'user_id' => $success ? $user->id : null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'data' => [
                    'email' => $user->email,
                    'success' => $success
                ],
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur journalisation connexion', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifier si une IP est bloquée
     */
    public static function isIpBlocked($ip)
    {
        $key = 'blocked_ip_' . md5($ip);
        return cache()->has($key);
    }

    /**
     * Bloquer une IP
     */
    public static function blockIp($ip, $duration = 3600)
    {
        $key = 'blocked_ip_' . md5($ip);
        cache()->put($key, true, $duration);
    }

    /**
     * Journaliser les alertes de sécurité
     */
    public static function logSecurityAlert($type, $data = [])
    {
        try {
            \App\Models\AuditLog::create([
                'action' => 'security_alert',
                'model_type' => 'Security',
                'model_id' => null,
                'user_id' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => array_merge($data, [
                    'alert_type' => $type,
                    'timestamp' => now()
                ]),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur journalisation alerte sécurité', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
        }
    }

    /**
     * Journaliser les actions sensibles d'authentification
     */
    public static function logAuthAction($action, $user = null, $data = [])
    {
        try {
            \App\Models\AuditLog::create([
                'action' => $action,
                'model_type' => 'Auth',
                'model_id' => $user ? $user->id : null,
                'user_id' => $user ? $user->id : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => array_merge($data, [
                    'user_email' => $user ? $user->email : null,
                    'timestamp' => now()
                ]),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur journalisation action auth', [
                'error' => $e->getMessage(),
                'action' => $action
            ]);
        }
    }

    /**
     * Journaliser les modifications de données sensibles
     */
    public static function logDataModification($action, $modelType, $modelId, $oldData = null, $newData = null, $userId = null)
    {
        try {
            \App\Models\AuditLog::create([
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'user_id' => $userId ?? auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => [
                    'old_data' => $oldData,
                    'new_data' => $newData,
                    'changes' => $oldData && $newData ? array_diff_assoc($newData, $oldData) : null,
                    'timestamp' => now()
                ],
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur journalisation modification données', [
                'error' => $e->getMessage(),
                'action' => $action,
                'model_type' => $modelType
            ]);
        }
    }

    /**
     * Journaliser les accès aux données sensibles
     */
    public static function logDataAccess($action, $modelType, $modelId = null, $data = [], $userId = null)
    {
        try {
            \App\Models\AuditLog::create([
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'user_id' => $userId ?? auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => array_merge($data, [
                    'timestamp' => now()
                ]),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur journalisation accès données', [
                'error' => $e->getMessage(),
                'action' => $action,
                'model_type' => $modelType
            ]);
        }
    }

    /**
     * Journaliser les suppressions de données
     */
    public static function logDataDeletion($modelType, $modelId, $deletedData = [], $userId = null)
    {
        try {
            \App\Models\AuditLog::create([
                'action' => 'delete',
                'model_type' => $modelType,
                'model_id' => $modelId,
                'user_id' => $userId ?? auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => array_merge($deletedData, [
                    'deleted_at' => now(),
                    'timestamp' => now()
                ]),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur journalisation suppression', [
                'error' => $e->getMessage(),
                'model_type' => $modelType,
                'model_id' => $modelId
            ]);
        }
    }
}
