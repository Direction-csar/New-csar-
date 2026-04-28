<?php

namespace App\Mail;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterNewsNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $news;
    public $unsubscribeEmail;

    public function __construct(News $news, string $unsubscribeEmail)
    {
        $this->news = $news;
        $this->unsubscribeEmail = $unsubscribeEmail;
    }

    public function build()
    {
        return $this->subject('Nouvelle publication CSAR : ' . $this->news->title)
                    ->view('emails.newsletter-news')
                    ->with([
                        'news' => $this->news,
                        'unsubscribeEmail' => $this->unsubscribeEmail,
                        'newsUrl' => url('/fr/actualites/' . $this->news->slug),
                    ]);
    }
}
