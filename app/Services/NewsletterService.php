<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\NewsletterSubscriber;

/**
 * Service d'intégration newsletter externe
 * Supporte : Mailchimp, SendGrid, Brevo (Sendinblue)
 */
class NewsletterService
{
    private $provider;
    private $apiKey;
    private $listId;

    public function __construct()
    {
        $this->provider = config('services.newsletter.provider', 'mailchimp');
        $this->apiKey = config('services.newsletter.api_key');
        $this->listId = config('services.newsletter.list_id');
    }

    /**
     * Ajouter un abonné
     */
    public function subscribe($email, $firstName = null, $lastName = null, $metadata = [])
    {
        try {
            switch ($this->provider) {
                case 'mailchimp':
                    return $this->mailchimpSubscribe($email, $firstName, $lastName, $metadata);
                
                case 'sendgrid':
                    return $this->sendgridSubscribe($email, $firstName, $lastName, $metadata);
                
                case 'brevo':
                    return $this->brevoSubscribe($email, $firstName, $lastName, $metadata);
                
                default:
                    return $this->localSubscribe($email, $firstName, $lastName);
            }
        } catch (\Exception $e) {
            Log::error('Erreur subscription newsletter', [
                'provider' => $this->provider,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            // Fallback vers base locale
            return $this->localSubscribe($email, $firstName, $lastName);
        }
    }

    /**
     * Désabonner un utilisateur
     */
    public function unsubscribe($email)
    {
        try {
            switch ($this->provider) {
                case 'mailchimp':
                    return $this->mailchimpUnsubscribe($email);
                
                case 'sendgrid':
                    return $this->sendgridUnsubscribe($email);
                
                case 'brevo':
                    return $this->brevoUnsubscribe($email);
                
                default:
                    return $this->localUnsubscribe($email);
            }
        } catch (\Exception $e) {
            Log::error('Erreur désabonnement newsletter', [
                'provider' => $this->provider,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return $this->localUnsubscribe($email);
        }
    }

    /**
     * Envoyer une campagne newsletter
     */
    public function sendCampaign($subject, $content, $segment = 'all')
    {
        try {
            switch ($this->provider) {
                case 'mailchimp':
                    return $this->mailchimpSendCampaign($subject, $content, $segment);
                
                case 'sendgrid':
                    return $this->sendgridSendCampaign($subject, $content, $segment);
                
                case 'brevo':
                    return $this->brevoSendCampaign($subject, $content, $segment);
                
                default:
                    return ['error' => 'Provider not configured'];
            }
        } catch (\Exception $e) {
            Log::error('Erreur envoi campagne', [
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtenir les statistiques d'une campagne
     */
    public function getCampaignStats($campaignId)
    {
        try {
            switch ($this->provider) {
                case 'mailchimp':
                    return $this->mailchimpGetStats($campaignId);
                
                case 'sendgrid':
                    return $this->sendgridGetStats($campaignId);
                
                case 'brevo':
                    return $this->brevoGetStats($campaignId);
                
                default:
                    return [];
            }
        } catch (\Exception $e) {
            Log::error('Erreur récupération stats', [
                'provider' => $this->provider,
                'campaign_id' => $campaignId,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    // ==================== MAILCHIMP ====================

    private function mailchimpSubscribe($email, $firstName, $lastName, $metadata)
    {
        $dc = explode('-', $this->apiKey)[1] ?? 'us1';
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$this->listId}/members";

        $response = Http::withBasicAuth('anystring', $this->apiKey)
            ->post($url, [
                'email_address' => $email,
                'status' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $firstName ?? '',
                    'LNAME' => $lastName ?? '',
                ],
                'tags' => $metadata['tags'] ?? [],
            ]);

        if ($response->successful()) {
            Log::info('Mailchimp: Abonné ajouté', ['email' => $email]);
            return ['success' => true, 'provider' => 'mailchimp'];
        }

        throw new \Exception($response->json()['detail'] ?? 'Mailchimp error');
    }

    private function mailchimpUnsubscribe($email)
    {
        $dc = explode('-', $this->apiKey)[1] ?? 'us1';
        $subscriberHash = md5(strtolower($email));
        $url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$this->listId}/members/{$subscriberHash}";

        $response = Http::withBasicAuth('anystring', $this->apiKey)
            ->patch($url, [
                'status' => 'unsubscribed'
            ]);

        return $response->successful();
    }

    private function mailchimpSendCampaign($subject, $content, $segment)
    {
        $dc = explode('-', $this->apiKey)[1] ?? 'us1';
        
        // 1. Créer la campagne
        $createUrl = "https://{$dc}.api.mailchimp.com/3.0/campaigns";
        
        $campaignData = [
            'type' => 'regular',
            'recipients' => [
                'list_id' => $this->listId,
            ],
            'settings' => [
                'subject_line' => $subject,
                'from_name' => config('services.newsletter.from_name', 'CSAR'),
                'reply_to' => config('services.newsletter.reply_to', 'noreply@csar.sn'),
                'title' => $subject,
            ],
        ];

        $response = Http::withBasicAuth('anystring', $this->apiKey)
            ->post($createUrl, $campaignData);

        if (!$response->successful()) {
            throw new \Exception('Failed to create campaign');
        }

        $campaignId = $response->json()['id'];

        // 2. Ajouter le contenu
        $contentUrl = "https://{$dc}.api.mailchimp.com/3.0/campaigns/{$campaignId}/content";
        
        Http::withBasicAuth('anystring', $this->apiKey)
            ->put($contentUrl, [
                'html' => $content
            ]);

        // 3. Envoyer
        $sendUrl = "https://{$dc}.api.mailchimp.com/3.0/campaigns/{$campaignId}/actions/send";
        
        $sendResponse = Http::withBasicAuth('anystring', $this->apiKey)
            ->post($sendUrl);

        return [
            'success' => $sendResponse->successful(),
            'campaign_id' => $campaignId
        ];
    }

    private function mailchimpGetStats($campaignId)
    {
        $dc = explode('-', $this->apiKey)[1] ?? 'us1';
        $url = "https://{$dc}.api.mailchimp.com/3.0/reports/{$campaignId}";

        $response = Http::withBasicAuth('anystring', $this->apiKey)
            ->get($url);

        if ($response->successful()) {
            $data = $response->json();
            
            return [
                'emails_sent' => $data['emails_sent'] ?? 0,
                'unique_opens' => $data['opens']['unique_opens'] ?? 0,
                'open_rate' => $data['opens']['open_rate'] ?? 0,
                'clicks' => $data['clicks']['unique_clicks'] ?? 0,
                'click_rate' => $data['clicks']['click_rate'] ?? 0,
                'unsubscribed' => $data['unsubscribed'] ?? 0,
            ];
        }

        return [];
    }

    // ==================== SENDGRID ====================

    private function sendgridSubscribe($email, $firstName, $lastName, $metadata)
    {
        $url = 'https://api.sendgrid.com/v3/marketing/contacts';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->put($url, [
            'list_ids' => [$this->listId],
            'contacts' => [
                [
                    'email' => $email,
                    'first_name' => $firstName ?? '',
                    'last_name' => $lastName ?? '',
                ]
            ]
        ]);

        if ($response->successful()) {
            Log::info('SendGrid: Abonné ajouté', ['email' => $email]);
            return ['success' => true, 'provider' => 'sendgrid'];
        }

        throw new \Exception($response->json()['errors'][0]['message'] ?? 'SendGrid error');
    }

    private function sendgridUnsubscribe($email)
    {
        // SendGrid gère les désabonnements via liens dans les emails
        // Marquer localement comme désabonné
        return $this->localUnsubscribe($email);
    }

    private function sendgridSendCampaign($subject, $content, $segment)
    {
        $url = 'https://api.sendgrid.com/v3/marketing/singlesends';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($url, [
            'name' => $subject,
            'send_to' => [
                'list_ids' => [$this->listId]
            ],
            'email_config' => [
                'subject' => $subject,
                'html_content' => $content,
                'sender_id' => config('services.newsletter.sender_id'),
            ],
        ]);

        if ($response->successful()) {
            $singleSendId = $response->json()['id'];
            
            // Programmer l'envoi immédiat
            $scheduleUrl = "https://api.sendgrid.com/v3/marketing/singlesends/{$singleSendId}/schedule";
            
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->put($scheduleUrl, [
                'send_at' => 'now'
            ]);

            return [
                'success' => true,
                'campaign_id' => $singleSendId
            ];
        }

        throw new \Exception('SendGrid campaign failed');
    }

    private function sendgridGetStats($campaignId)
    {
        $url = "https://api.sendgrid.com/v3/marketing/stats/singlesends/{$campaignId}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $stats = $data['results'][0]['stats'] ?? [];

            return [
                'emails_sent' => $stats['requests'] ?? 0,
                'delivered' => $stats['delivered'] ?? 0,
                'unique_opens' => $stats['unique_opens'] ?? 0,
                'clicks' => $stats['unique_clicks'] ?? 0,
                'unsubscribed' => $stats['unsubscribes'] ?? 0,
            ];
        }

        return [];
    }

    // ==================== BREVO (Sendinblue) ====================

    private function brevoSubscribe($email, $firstName, $lastName, $metadata)
    {
        $url = 'https://api.brevo.com/v3/contacts';

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'email' => $email,
            'attributes' => [
                'FIRSTNAME' => $firstName ?? '',
                'LASTNAME' => $lastName ?? '',
            ],
            'listIds' => [(int)$this->listId],
            'updateEnabled' => true,
        ]);

        if ($response->successful() || $response->status() === 201) {
            Log::info('Brevo: Abonné ajouté', ['email' => $email]);
            return ['success' => true, 'provider' => 'brevo'];
        }

        throw new \Exception($response->json()['message'] ?? 'Brevo error');
    }

    private function brevoUnsubscribe($email)
    {
        $url = "https://api.brevo.com/v3/contacts/{$email}";

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
        ])->delete($url);

        return $response->successful();
    }

    private function brevoSendCampaign($subject, $content, $segment)
    {
        $url = 'https://api.brevo.com/v3/emailCampaigns';

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
        ])->post($url, [
            'name' => $subject,
            'subject' => $subject,
            'sender' => [
                'name' => config('services.newsletter.from_name', 'CSAR'),
                'email' => config('services.newsletter.reply_to', 'noreply@csar.sn'),
            ],
            'type' => 'classic',
            'htmlContent' => $content,
            'recipients' => [
                'listIds' => [(int)$this->listId]
            ],
            'inlineImageActivation' => true,
        ]);

        if ($response->successful()) {
            $campaignId = $response->json()['id'];
            
            // Envoyer immédiatement
            $sendUrl = "https://api.brevo.com/v3/emailCampaigns/{$campaignId}/sendNow";
            
            Http::withHeaders([
                'api-key' => $this->apiKey,
            ])->post($sendUrl);

            return [
                'success' => true,
                'campaign_id' => $campaignId
            ];
        }

        throw new \Exception('Brevo campaign failed');
    }

    private function brevoGetStats($campaignId)
    {
        $url = "https://api.brevo.com/v3/emailCampaigns/{$campaignId}";

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $stats = $data['statistics'] ?? [];

            return [
                'emails_sent' => $stats['sent'] ?? 0,
                'unique_opens' => $stats['uniqueOpens'] ?? 0,
                'open_rate' => $stats['openRate'] ?? 0,
                'clicks' => $stats['uniqueClicks'] ?? 0,
                'click_rate' => $stats['clickRate'] ?? 0,
                'unsubscribed' => $stats['unsubscriptions'] ?? 0,
            ];
        }

        return [];
    }

    // ==================== LOCAL (FALLBACK) ====================

    private function localSubscribe($email, $firstName, $lastName)
    {
        NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'is_active' => true,
                'subscribed_at' => now(),
            ]
        );

        Log::info('Local: Abonné ajouté', ['email' => $email]);
        
        return ['success' => true, 'provider' => 'local'];
    }

    private function localUnsubscribe($email)
    {
        NewsletterSubscriber::where('email', $email)
            ->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
            ]);

        return true;
    }
}






































