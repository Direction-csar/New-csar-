<?php

namespace App\Listeners;

use App\Events\CommunicationPublished;
use App\Mail\NewsletterNewsNotification;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewsletterToSubscribers implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CommunicationPublished $event): void
    {
        $news = $event->communication;

        if (!$news->is_public) {
            return;
        }

        $subscribers = NewsletterSubscriber::where('status', 'active')->get();

        if ($subscribers->isEmpty()) {
            return;
        }

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)
                    ->queue(new NewsletterNewsNotification($news, $subscriber->email));
            } catch (\Exception $e) {
                Log::error('Erreur envoi newsletter abonné', [
                    'email' => $subscriber->email,
                    'news_id' => $news->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Newsletter envoyée aux abonnés', [
            'news_id' => $news->id,
            'title' => $news->title,
            'subscribers_count' => $subscribers->count()
        ]);
    }
}
