<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

class BrevoApiTransport implements TransportInterface
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

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        if (!$message instanceof Email) {
            return new SentMessage($message, $envelope ?? Envelope::create($message));
        }

        $payload = [
            'sender' => [
                'email' => $this->senderEmail,
                'name' => $this->senderName,
            ],
            'subject' => $message->getSubject() ?? '(Sans sujet)',
        ];

        $to = [];
        foreach ($message->getTo() as $addr) {
            $item = ['email' => $addr->getAddress()];
            if ($addr->getName()) {
                $item['name'] = $addr->getName();
            }
            $to[] = $item;
        }
        $payload['to'] = $to;

        $cc = [];
        foreach ($message->getCc() as $addr) {
            $cc[] = ['email' => $addr->getAddress(), 'name' => $addr->getName()];
        }
        if ($cc) {
            $payload['cc'] = $cc;
        }

        $bcc = [];
        foreach ($message->getBcc() as $addr) {
            $bcc[] = ['email' => $addr->getAddress(), 'name' => $addr->getName()];
        }
        if ($bcc) {
            $payload['bcc'] = $bcc;
        }

        if ($message->getReplyTo()) {
            $replyTo = $message->getReplyTo()[0];
            $payload['replyTo'] = ['email' => $replyTo->getAddress()];
            if ($replyTo->getName()) {
                $payload['replyTo']['name'] = $replyTo->getName();
            }
        }

        $htmlBody = $message->getHtmlBody();
        $textBody = $message->getTextBody();

        if ($htmlBody) {
            $payload['htmlContent'] = $htmlBody;
        } elseif ($textBody) {
            $payload['textContent'] = $textBody;
            $payload['htmlContent'] = nl2br(e($textBody));
        } else {
            $payload['htmlContent'] = '';
        }

        if ($textBody) {
            $payload['textContent'] = $textBody;
        }

        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            $attachments[] = [
                'name' => $attachment->getFilename(),
                'content' => base64_encode($attachment->getBody()),
            ];
        }
        if ($attachments) {
            $payload['attachment'] = $attachments;
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
        ])->post($this->baseUrl . '/smtp/email', $payload);

        if (!$response->successful()) {
            $error = $response->json()['message'] ?? $response->body();
            throw new \Exception('Brevo API Transport: ' . $error);
        }

        $messageId = $response->json()['messageId'] ?? null;

        $sentMessage = new SentMessage($message, $envelope ?? Envelope::create($message));
        return $sentMessage;
    }

    public function __toString(): string
    {
        return 'brevo-api://';
    }
}
