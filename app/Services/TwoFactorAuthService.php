<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Générer une clé secrète pour l'authentification à deux facteurs
     */
    public function generateSecretKey()
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Générer l'URL QR Code pour l'application d'authentification
     */
    public function getQRCodeUrl($user, $secretKey)
    {
        $companyName = 'CSAR Platform';
        $companyEmail = $user->email;
        
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $secretKey
        );
    }

    /**
     * Vérifier le code 2FA
     * @param int $window Tolérance en périodes de 30s (4 = ±2 min)
     */
    public function verifyCode($secretKey, $code, $window = 4)
    {
        try {
            $result = $this->google2fa->verifyKey($secretKey, $code, $window);

            Log::info('Vérification 2FA', [
                'result' => $result ? 'succès' : 'échec',
                'code_length' => strlen($code),
                'window' => $window,
                'server_time' => now()->toDateTimeString(),
                'secret_length' => strlen($secretKey),
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Erreur vérification 2FA', [
                'error' => $e->getMessage(),
                'code_length' => strlen($code),
            ]);
            return false;
        }
    }

    /**
     * Activer la 2FA pour un utilisateur
     */
    public function enableTwoFactor($userId, $secretKey, $verificationCode)
    {
        try {
            if (!$this->verifyCode($secretKey, $verificationCode)) {
                return [
                    'success' => false,
                    'message' => 'Code de vérification invalide'
                ];
            }

            $user = User::findOrFail($userId);
            $user->update([
                'two_factor_secret' => encrypt($secretKey),
                'two_factor_enabled' => true,
                'two_factor_verified_at' => now()
            ]);

            // Log de l'activation
            SecurityService::logAudit('2fa_enabled', 'User', $userId, [
                'user_email' => $user->email,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => true,
                'message' => 'Authentification à deux facteurs activée avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur activation 2FA', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'activation de la 2FA'
            ];
        }
    }

    /**
     * Désactiver la 2FA pour un utilisateur
     */
    public function disableTwoFactor($userId, $verificationCode)
    {
        try {
            $user = User::findOrFail($userId);
            
            if (!$user->two_factor_enabled) {
                return [
                    'success' => false,
                    'message' => 'La 2FA n\'est pas activée pour cet utilisateur'
                ];
            }

            $secretKey = decrypt($user->two_factor_secret);
            
            if (!$this->verifyCode($secretKey, $verificationCode)) {
                return [
                    'success' => false,
                    'message' => 'Code de vérification invalide'
                ];
            }

            $user->update([
                'two_factor_secret' => null,
                'two_factor_enabled' => false,
                'two_factor_verified_at' => null
            ]);

            // Log de la désactivation
            SecurityService::logAudit('2fa_disabled', 'User', $userId, [
                'user_email' => $user->email,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => true,
                'message' => 'Authentification à deux facteurs désactivée avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur désactivation 2FA', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la désactivation de la 2FA'
            ];
        }
    }

    /**
     * Vérifier si la 2FA est requise pour la connexion
     */
    public function isTwoFactorRequired($user)
    {
        return $user->two_factor_enabled && $user->role === 'admin';
    }

    /**
     * Générer des codes de récupération
     */
    public function generateRecoveryCodes($userId)
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }

        // Stocker les codes chiffrés
        $user = User::findOrFail($userId);
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($codes))
        ]);

        return $codes;
    }

    /**
     * Vérifier un code de récupération
     */
    public function verifyRecoveryCode($userId, $code)
    {
        try {
            $user = User::findOrFail($userId);
            
            if (!$user->two_factor_recovery_codes) {
                return false;
            }

            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            
            if (in_array(strtoupper($code), $recoveryCodes)) {
                // Supprimer le code utilisé
                $recoveryCodes = array_diff($recoveryCodes, [strtoupper($code)]);
                $user->update([
                    'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes))
                ]);

                // Log de l'utilisation du code de récupération
                SecurityService::logAudit('2fa_recovery_used', 'User', $userId, [
                    'user_email' => $user->email,
                    'ip_address' => request()->ip()
                ]);

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Erreur vérification code récupération 2FA', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
