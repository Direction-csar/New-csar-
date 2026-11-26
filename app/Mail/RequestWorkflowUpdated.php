<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RequestWorkflowUpdated extends Mailable
{
    public function __construct(public string $reference, public string $statusLabel)
    {
    }

    public function build(): static
    {
        return $this->subject('Mise à jour de votre demande CSAR')
            ->text('emails.request-status-secure');
    }
}
