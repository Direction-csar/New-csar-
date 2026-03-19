<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsNotification;

/**
 * Service d'envoi SMS
 * Supporte : Orange (Sénégal), Twilio, Vonage (Nexmo), InfoBip, AfricasTalking
 */
class SmsService
{
    private $provider;
    private $config;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'twilio');
        $this->config = config("services.sms.{$this->provider}", []);
    }

    /**
     * Envoyer un SMS
     */
    public function send($to, $message, $priority = 'normal')
    {
        try {
            // Normaliser le numéro
            $to = $this->normalizePhoneNumber($to);

            // Vérifier le crédit/quota
            if (!$this->checkQuota()) {
                throw new \Exception('Quota SMS dépassé');
            }

            // Envoyer selon le provider
            $result = match($this->provider) {
                'orange' => $this->sendViaOrange($to, $message),
                'twilio' => $this->sendViaTwilio($to, $message),
                'vonage' => $this->sendViaVonage($to, $message),
                'infobip' => $this->sendViaInfoBip($to, $message),
                'africastalking' => $this->sendViaAfricasTalking($to, $message),
                default => throw new \Exception('Provider SMS non configuré')
            };

            // Enregistrer dans la base
            $this->logSms($to, $message, $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur envoi SMS', [
                'provider' => $this->provider,
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Envoyer un SMS groupé
     */
    public function sendBulk(array $recipients, $message)
    {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                $results[$recipient] = $this->send($recipient, $message);
            } catch (\Exception $e) {
                $results[$recipient] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Envoyer une alerte SMS critique
     */
    public function sendAlert($to, $title, $message)
    {
        $fullMessage = "🚨 ALERTE CSAR 🚨\n\n{$title}\n\n{$message}\n\nEnvoyé: " . now()->format('d/m/Y H:i');
        
        return $this->send($to, $fullMessage, 'high');
    }

    /**
     * Envoyer un OTP (One-Time Password)
     */
    public function sendOTP($to, $code, $expiresIn = 10)
    {
        $message = "Code de vérification CSAR: {$code}\n\nValable {$expiresIn} minutes.\nNe partagez pas ce code.";
        
        return $this->send($to, $message, 'high');
    }

    /**
     * Envoyer une confirmation de demande (alias pour les formulaires publics)
     * @param string|object $phoneOrRequest Numéro ou objet PublicRequest
     * @param string|null $trackingCode Code de suivi (si $phoneOrRequest est un numéro)
     * @param string $type Type de demande (si $phoneOrRequest est un numéro)
     */
    public function sendRequestConfirmation($phoneOrRequest, $trackingCode = null, $type = 'demande')
    {
        if (is_object($phoneOrRequest)) {
            $phone = $phoneOrRequest->phone ?? $phoneOrRequest->phone_number ?? null;
            $trackingCode = $phoneOrRequest->tracking_code ?? $phoneOrRequest->trackingCode ?? '';
            $type = $phoneOrRequest->type ?? 'autre';
        } else {
            $phone = $phoneOrRequest;
        }
        if (!$phone) {
            throw new \Exception('Numéro de téléphone requis pour l\'envoi SMS');
        }
        $messages = [
            'aide' => "Votre demande d'aide a bien été transmise au CSAR. Code de suivi: {$trackingCode}. Nous vous contacterons sous 24-48h.",
            'partenariat' => "Votre demande de partenariat a bien été transmise au CSAR. Code de suivi: {$trackingCode}.",
            'audience' => "Votre demande d'audience a bien été transmise au CSAR. Code de suivi: {$trackingCode}.",
            'autre' => "Votre demande a bien été transmise au CSAR. Code de suivi: {$trackingCode}. Nous vous contacterons prochainement.",
        ];
        $message = $messages[$type] ?? $messages['autre'];
        return $this->send($phone, $message);
    }

    /**
     * Alias pour send() - compatibilité avec le code existant
     */
    public function sendSms($phone, $message, $type = 'notification')
    {
        return $this->send($phone, $message);
    }

    /**
     * Envoyer via SmsNotification (pour ProcessSmsNotification job)
     */
    public function sendToProvider(SmsNotification $notification)
    {
        $result = $this->send($notification->phone_number, $notification->message);
        return ['success' => $result['success'] ?? false, 'error' => null];
    }

    // ==================== ORANGE SMS SÉNÉGAL ====================

    private function sendViaOrange($to, $message)
    {
        $clientId = $this->config['client_id'] ?? null;
        $clientSecret = $this->config['client_secret'] ?? null;
        $senderNumber = $this->config['sender_number'] ?? '2210000';

        if (!$clientId || !$clientSecret) {
            throw new \Exception('Orange SMS: Client ID et Client Secret requis dans .env (ORANGE_SMS_CLIENT_ID, ORANGE_SMS_CLIENT_SECRET)');
        }

        $token = $this->getOrangeAccessToken($clientId, $clientSecret);
        $senderEncoded = 'tel%3A%2B' . $senderNumber;
        $url = "https://api.orange.com/smsmessaging/v1/outbound/{$senderEncoded}/requests";

        $body = [
            'outboundSMSMessageRequest' => [
                'address' => 'tel:' . $to,
                'senderAddress' => 'tel:+' . $senderNumber,
                'outboundSMSTextMessage' => [
                    'message' => $message,
                ],
            ],
        ];

        $response = Http::withToken($token)
            ->asJson()
            ->post($url, $body);

        if ($response->successful()) {
            $data = $response->json();
            $resourceUrl = $data['outboundSMSMessageRequest']['resourceURL'] ?? null;
            $resourceId = $resourceUrl ? basename(parse_url($resourceUrl, PHP_URL_PATH)) : null;

            return [
                'success' => true,
                'provider' => 'orange',
                'message_id' => $resourceId,
                'status' => 'sent',
            ];
        }

        $errorBody = $response->json();
        $errorMsg = $errorBody['message'] ?? $errorBody['description'] ?? 'Orange SMS error';
        if ($response->status() === 401 && ($errorBody['code'] ?? 0) === 42) {
            throw new \Exception('Orange SMS: Token expiré. Réessayez.');
        }
        throw new \Exception('Orange SMS: ' . $errorMsg);
    }

    private function getOrangeAccessToken($clientId, $clientSecret)
    {
        $cacheKey = 'orange_sms_token';
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            $err = $response->json();
            throw new \Exception('Orange OAuth: ' . ($err['message'] ?? $err['error_description'] ?? 'Échec authentification'));
        }

        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        \Illuminate\Support\Facades\Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 60));

        return $token;
    }

    // ==================== TWILIO ====================

    private function sendViaTwilio($to, $message)
    {
        $sid = $this->config['account_sid'];
        $token = $this->config['auth_token'];
        $from = $this->config['from_number'];

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post($url, [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            
            return [
                'success' => true,
                'provider' => 'twilio',
                'message_id' => $data['sid'] ?? null,
                'status' => $data['status'] ?? 'sent',
            ];
        }

        throw new \Exception($response->json()['message'] ?? 'Twilio error');
    }

    // ==================== VONAGE (NEXMO) ====================

    private function sendViaVonage($to, $message)
    {
        $apiKey = $this->config['api_key'];
        $apiSecret = $this->config['api_secret'];
        $from = $this->config['from'];

        $url = 'https://rest.nexmo.com/sms/json';

        $response = Http::post($url, [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'from' => $from,
            'to' => $to,
            'text' => $message,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $msg = $data['messages'][0] ?? [];

            if ($msg['status'] == '0') {
                return [
                    'success' => true,
                    'provider' => 'vonage',
                    'message_id' => $msg['message-id'] ?? null,
                    'status' => 'sent',
                ];
            }
        }

        throw new \Exception($response->json()['messages'][0]['error-text'] ?? 'Vonage error');
    }

    // ==================== INFOBIP ====================

    private function sendViaInfoBip($to, $message)
    {
        $apiKey = $this->config['api_key'];
        $baseUrl = $this->config['base_url'] ?? 'https://api.infobip.com';
        $from = $this->config['from'];

        $url = "{$baseUrl}/sms/2/text/advanced";

        $response = Http::withHeaders([
            'Authorization' => "App {$apiKey}",
            'Content-Type' => 'application/json',
        ])->post($url, [
            'messages' => [
                [
                    'from' => $from,
                    'destinations' => [
                        ['to' => $to]
                    ],
                    'text' => $message,
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $msg = $data['messages'][0] ?? [];

            return [
                'success' => true,
                'provider' => 'infobip',
                'message_id' => $msg['messageId'] ?? null,
                'status' => $msg['status']['name'] ?? 'sent',
            ];
        }

        throw new \Exception('InfoBip error');
    }

    // ==================== AFRICA'S TALKING ====================

    private function sendViaAfricasTalking($to, $message)
    {
        $apiKey = $this->config['api_key'];
        $username = $this->config['username'];
        $from = $this->config['from'] ?? 'CSAR';

        $url = 'https://api.africastalking.com/version1/messaging';

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, [
            'username' => $username,
            'to' => $to,
            'message' => $message,
            'from' => $from,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $msg = $data['SMSMessageData']['Recipients'][0] ?? [];

            if (isset($msg['status']) && $msg['status'] === 'Success') {
                return [
                    'success' => true,
                    'provider' => 'africastalking',
                    'message_id' => $msg['messageId'] ?? null,
                    'status' => 'sent',
                ];
            }
        }

        throw new \Exception('Africa\'s Talking error');
    }

    // ==================== HELPERS ====================

    /**
     * Normaliser un numéro de téléphone
     */
    private function normalizePhoneNumber($phone)
    {
        // Retirer espaces, tirets, points
        $phone = preg_replace('/[\s\-\.]/', '', $phone);

        // Ajouter +221 si manquant (Sénégal)
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '221')) {
                $phone = '+' . $phone;
            } elseif (str_starts_with($phone, '0')) {
                $phone = '+221' . substr($phone, 1);
            } else {
                $phone = '+221' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Vérifier le quota SMS
     */
    private function checkQuota()
    {
        // Vérifier le quota mensuel
        $maxPerMonth = config('services.sms.max_per_month', 1000);
        
        $sentThisMonth = SmsNotification::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $sentThisMonth < $maxPerMonth;
    }

    /**
     * Enregistrer le SMS envoyé
     */
    private function logSms($to, $message, $result)
    {
        try {
            SmsNotification::create([
                'phone_number' => $to,
                'message' => $message,
                'type' => 'system_alert',
                'status' => ($result['success'] ?? false) ? 'sent' : 'failed',
                'sent_at' => ($result['success'] ?? false) ? now() : null,
                'error_message' => ($result['success'] ?? false) ? null : ($result['error'] ?? 'Unknown'),
            ]);
        } catch (\Exception $e) {
            Log::warning('Impossible d\'enregistrer le SMS dans sms_notifications', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les statistiques SMS
     */
    public function getStats($period = '30days')
    {
        $since = match($period) {
            '24h' => now()->subDay(),
            '7days' => now()->subWeek(),
            '30days' => now()->subMonth(),
            '1year' => now()->subYear(),
            default => now()->subMonth()
        };

        return [
            'total_sent' => SmsNotification::where('created_at', '>=', $since)->count(),
            'successful' => SmsNotification::where('created_at', '>=', $since)
                ->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => SmsNotification::where('created_at', '>=', $since)
                ->where('status', 'failed')->count(),
            'by_provider' => collect([['provider' => $this->provider, 'count' => SmsNotification::where('created_at', '>=', $since)->count()]]),
        ];
    }
}
