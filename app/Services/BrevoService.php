<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service Brevo (ex-Sendinblue) — API REST v3
 * 
 * Fonctionnalités :
 * - Envoi d'emails transactionnels (avec ou sans template)
 * - Gestion de contacts (création, mise à jour, ajout à une liste)
 * - Envoi de SMS transactionnels
 * 
 * Documentation : https://developers.brevo.com/
 * Clés API : https://app.brevo.com/settings/keys/api
 */
class BrevoService
{
    private string $apiKey;
    private string $baseUrl;
    private string $senderEmail;
    private string $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key', '');
        $this->baseUrl = config('services.brevo.base_url', 'https://api.brevo.com/v3');
        $this->senderEmail = config('services.brevo.sender_email', 'noreply@csar.sn');
        $this->senderName = config('services.brevo.sender_name', 'CSAR');
    }

    /**
     * Envoyer un email transactionnel
     *
     * @param string|array $to Adresse email ou tableau ['email' => ..., 'name' => ...]
     * @param string $subject Sujet de l'email
     * @param string $htmlContent Contenu HTML
     * @param array $options Options : cc, bcc, replyTo, attachments, templateId, params
     * @return array Réponse de l'API
     */
    public function sendEmail(string|array $to, string $subject, string $htmlContent = '', array $options = []): array
    {
        $recipient = is_array($to) ? $to : ['email' => $to];

        $payload = [
            'sender' => [
                'email' => $this->senderEmail,
                'name' => $this->senderName,
            ],
            'to' => [$recipient],
            'subject' => $subject,
        ];

        // Utiliser un template Brevo si fourni
        if (!empty($options['templateId'])) {
            $payload['templateId'] = (int) $options['templateId'];
            if (!empty($options['params'])) {
                $payload['params'] = $options['params'];
            }
        } else {
            $payload['htmlContent'] = $htmlContent;
        }

        // Options supplémentaires
        if (!empty($options['cc'])) {
            $payload['cc'] = array_map(fn($email) => ['email' => $email], (array) $options['cc']);
        }
        if (!empty($options['bcc'])) {
            $payload['bcc'] = array_map(fn($email) => ['email' => $email], (array) $options['bcc']);
        }
        if (!empty($options['replyTo'])) {
            $payload['replyTo'] = ['email' => $options['replyTo']];
        }
        if (!empty($options['attachments'])) {
            $payload['attachment'] = $options['attachments'];
        }
        if (!empty($options['tags'])) {
            $payload['tags'] = (array) $options['tags'];
        }

        $response = $this->request('POST', '/smtp/email', $payload);

        return [
            'success' => true,
            'message_id' => $response['messageId'] ?? null,
        ];
    }

    /**
     * Envoyer un email via un template Brevo
     *
     * @param string $to Adresse email du destinataire
     * @param int $templateId ID du template Brevo
     * @param array $params Variables à injecter dans le template
     * @return array
     */
    public function sendTemplateEmail(string $to, int $templateId, array $params = []): array
    {
        return $this->sendEmail($to, '', '', [
            'templateId' => $templateId,
            'params' => $params,
        ]);
    }

    /**
     * Créer ou mettre à jour un contact dans Brevo
     *
     * @param string $email Email du contact
     * @param array $attributes Attributs (nom, prénom, téléphone, etc.)
     * @param array|null $listIds IDs des listes auxquelles ajouter le contact
     * @param bool $updateEnabled Autoriser la mise à jour si le contact existe
     * @return array
     */
    public function createOrUpdateContact(string $email, array $attributes = [], ?array $listIds = null, bool $updateEnabled = true): array
    {
        $payload = [
            'email' => $email,
            'attributes' => $attributes,
            'updateEnabled' => $updateEnabled,
        ];

        if ($listIds) {
            $payload['listIds'] = array_map('intval', $listIds);
        }

        $this->request('POST', '/contacts', $payload);

        return ['success' => true, 'email' => $email];
    }

    /**
     * Ajouter un contact à une liste
     *
     * @param string $email Email du contact
     * @param int $listId ID de la liste
     * @return array
     */
    public function addContactToList(string $email, int $listId): array
    {
        $this->request('POST', '/contacts/lists/' . $listId . '/contacts/add', [
            'emails' => [$email],
        ]);

        return ['success' => true];
    }

    /**
     * Récupérer les informations d'un contact
     *
     * @param string $email
     * @return array|null
     */
    public function getContact(string $email): ?array
    {
        try {
            return $this->request('GET', '/contacts/' . urlencode($email));
        } catch (\Exception $e) {
            Log::warning('Brevo: Contact non trouvé', ['email' => $email, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Supprimer un contact
     *
     * @param string $email
     * @return bool
     */
    public function deleteContact(string $email): bool
    {
        try {
            $this->request('DELETE', '/contacts/' . urlencode($email));
            return true;
        } catch (\Exception $e) {
            Log::error('Brevo: Erreur suppression contact', ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Envoyer un SMS transactionnel via Brevo
     *
     * @param string $to Numéro au format international (ex: +22177000000)
     * @param string $message Contenu du SMS
     * @param string|null $sender Nom de l'expéditeur (max 11 caractères)
     * @return array
     */
    public function sendSms(string $to, string $message, ?string $sender = null): array
    {
        $sender = $sender ?: config('services.brevo.sms_sender', 'CSAR');

        $response = $this->request('POST', '/transactionalSMS/sms', [
            'sender' => $sender,
            'recipient' => $to,
            'content' => $message,
            'type' => 'transactional',
        ]);

        return [
            'success' => true,
            'message_id' => $response['messageId'] ?? $response['messageIds'][0] ?? null,
        ];
    }

    /**
     * Récupérer les listes de contacts
     *
     * @return array
     */
    public function getLists(): array
    {
        return $this->request('GET', '/contacts/lists');
    }

    /**
     * Créer une liste de contacts
     *
     * @param string $name Nom de la liste
     * @param string|null $folderId ID du dossier (optionnel)
     * @return array
     */
    public function createList(string $name, ?string $folderId = null): array
    {
        $payload = ['name' => $name, 'folderId' => $folderId ? (int) $folderId : 1];

        return $this->request('POST', '/contacts/lists', $payload);
    }

    /**
     * Méthode interne : effectuer une requête HTTP vers l'API Brevo
     */
    private function request(string $method, string $endpoint, ?array $data = null): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Brevo: API key non configurée. Définissez BREVO_API_KEY dans le .env');
        }

        $url = $this->baseUrl . $endpoint;

        $http = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ]);

        $response = match (strtoupper($method)) {
            'GET' => $http->get($url),
            'POST' => $http->post($url, $data ?? []),
            'PUT' => $http->put($url, $data ?? []),
            'DELETE' => $http->delete($url),
            default => throw new \Exception("Méthode HTTP non supportée: {$method}"),
        };

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $errorBody = $response->json();
        $errorMsg = $errorBody['message'] ?? $errorBody['error'] ?? $response->body();

        Log::error('Brevo API Error', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'error' => $errorMsg,
        ]);

        throw new \Exception('Brevo API: ' . $errorMsg);
    }
}
