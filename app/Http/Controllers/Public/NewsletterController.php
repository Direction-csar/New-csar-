<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255'
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $email = $request->email;
            $existingSubscriber = NewsletterSubscriber::where('email', $email)->first();

            if ($existingSubscriber) {
                if ($existingSubscriber->status === 'active') {
                    $message = 'Cette adresse email est déjà inscrite à notre newsletter.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }
                    return back()->with('error', $message);
                }

                if ($existingSubscriber->status === 'pending') {
                    $this->sendConfirmationEmail($existingSubscriber);
                    $message = 'Un email de confirmation vous a été renvoyé. Vérifiez votre boîte mail.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => true, 'message' => $message]);
                    }
                    return back()->with('success', $message);
                }

                // Réactiver (unsubscribed → pending re-confirmation)
                $token = Str::random(64);
                $existingSubscriber->update([
                    'status' => 'pending',
                    'confirmation_token' => $token,
                    'confirmed_at' => null,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);
                $this->sendConfirmationEmail($existingSubscriber);
                $message = 'Vérifiez votre email pour confirmer votre réabonnement.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }
                return back()->with('success', $message);
            }

            // Nouvelle inscription — en attente de confirmation
            $token = Str::random(64);
            $subscriber = NewsletterSubscriber::create([
                'email'              => $email,
                'status'             => 'pending',
                'confirmation_token' => $token,
                'subscribed_at'      => now(),
                'source'             => 'website',
            ]);

            $this->sendConfirmationEmail($subscriber);

            $message = 'Merci ! Vérifiez votre email pour confirmer votre abonnement à la newsletter CSAR.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Erreur abonnement newsletter: ' . $e->getMessage());
            $message = 'Une erreur est survenue. Veuillez réessayer.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
    }

    public function confirm(string $token)
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$subscriber) {
            return redirect('/')->with('error', 'Lien de confirmation invalide ou déjà utilisé.');
        }

        $subscriber->confirm();

        try {
            \App\Models\Notification::create([
                'type'    => 'info',
                'title'   => 'Nouvel abonnement newsletter confirmé',
                'message' => "Abonnement confirmé : {$subscriber->email}",
                'user_id' => null,
                'read'    => false,
            ]);
        } catch (\Exception $e) {
            \Log::error('Notification newsletter: ' . $e->getMessage());
        }

        return redirect('/')->with('success', '✅ Votre abonnement à la newsletter CSAR est confirmé ! Vous recevrez nos actualités régulièrement.');
    }

    private function sendConfirmationEmail(NewsletterSubscriber $subscriber): void
    {
        try {
            $confirmUrl = url('/newsletter/confirm/' . $subscriber->confirmation_token);

            Mail::send('emails.newsletter.confirm', [
                'subscriber'  => $subscriber,
                'confirmUrl'  => $confirmUrl,
            ], function ($message) use ($subscriber) {
                $message->to($subscriber->email)
                    ->subject('Confirmez votre abonnement à la newsletter CSAR')
                    ->from(config('mail.from.address', 'noreply@csar.sn'), config('mail.from.name', 'CSAR'));
            });
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email confirmation newsletter: ' . $e->getMessage());
        }
    }

    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator);
        }

        try {
            $subscriber = NewsletterSubscriber::where('email', $request->email)->first();

            if (!$subscriber) {
                $message = 'Cette adresse email n\'est pas inscrite à notre newsletter.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->with('error', $message);
            }

            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()
            ]);

            $message = 'Vous avez été désinscrit de notre newsletter.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Erreur désabonnement newsletter: ' . $e->getMessage());
            
            $message = 'Une erreur est survenue lors du désabonnement. Veuillez réessayer.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
        }
    }

    public function checkSubscription(Request $request)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse email requise'
            ], 400);
        }

        $subscription = NewsletterSubscriber::where('email', $email)->first();
        
        return response()->json([
            'success' => true,
            'is_subscribed' => $subscription ? ($subscription->status === 'active') : false,
            'subscribed_at' => $subscription ? $subscription->subscribed_at : null
        ]);
    }

    /**
     * Afficher la page de désinscription
     */
    public function unsubscribePage()
    {
        return view('newsletter.unsubscribe');
    }
}
