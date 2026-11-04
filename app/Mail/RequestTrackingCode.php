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
            ->view('emails.request-tracking-code-html')
            ->text('emails.request-tracking-code');
    }
}
