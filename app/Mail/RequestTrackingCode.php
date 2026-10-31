<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RequestTrackingCode extends Mailable
{
    public function __construct(public string $code)
    {
    }

    public function build(): static
    {
        return $this->subject('Code de vérification de votre demande CSAR')
            ->text('emails.request-tracking-code');
    }
}
