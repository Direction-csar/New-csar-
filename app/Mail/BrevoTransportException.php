<?php

namespace App\Mail;

use Symfony\Component\Mailer\Exception\TransportException;

/**
 * Refus ou échec du fournisseur Brevo. Ne contient que le code d'erreur
 * fournisseur et le statut HTTP : jamais le contenu, le destinataire ni la clé.
 */
class BrevoTransportException extends TransportException
{
    public function __construct(public readonly string $providerCode, public readonly int $httpStatus)
    {
        parent::__construct("Brevo API Transport: {$providerCode} (HTTP {$httpStatus})");
    }
}
