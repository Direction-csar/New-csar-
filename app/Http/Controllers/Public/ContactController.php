<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        try {
            return view('public.contact');
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une vue simple
            \Log::error('Erreur dans ContactController: ' . $e->getMessage());
            return view('public.contact-simple');
        }
    }
    
    public function submit(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'nullable|string|max:20',
            'subject'       => 'required|string|max:255',
            'subject_autre' => 'required_if:subject,Autre|nullable|string|max:255',
            'message'       => 'required|string'
        ]);

        if ($request->subject === 'Autre' && $request->subject_autre) {
            $request->merge(['subject' => 'Autre : ' . $request->subject_autre]);
        }

        $fullName = $request->name;

        // Vérifier les doublons
        if (\App\Services\SecurityService::checkDuplicateContact($request->email, $request->subject, $request->message)) {
            return back()->with('error', 'Un message similaire a déjà été envoyé récemment. Veuillez attendre avant de renvoyer le même message.');
        }

        // Générer le hash de duplication
        $duplicateHash = \App\Services\SecurityService::generateDuplicateHash($request->email, $request->subject, $request->message);

        $contact = ContactMessage::create([
            'full_name' => $fullName,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'duplicate_hash' => $duplicateHash
        ]);

        // Journaliser la création du contact
        \App\Services\SecurityService::logAudit('contact_created', 'ContactMessage', $contact->id, [
            'email' => $request->email,
            'subject' => $request->subject,
            'duplicate_hash' => $duplicateHash
        ]);

        // Créer aussi un message dans la table admin pour la gestion
        $adminMessage = \App\Models\Message::create([
            'sujet' => $request->subject,
            'contenu' => $request->message,
            'expediteur' => $fullName,
            'email_expediteur' => $request->email,
            'telephone_expediteur' => $request->phone,
            'lu' => false,
            'reponse' => null,
        ]);

        // Déclencher l'événement pour créer une notification automatique
        event(new \App\Events\MessageReceived($contact));

        // Envoyer un email de notification aux administrateurs
        try {
            $emailService = new \App\Services\AdminEmailService();
            $emailService->sendContactMessageNotification($contact);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email notification contact: ' . $e->getMessage());
        }

        // Envoyer un email de confirmation à l'utilisateur
        try {
            $emailService = new \App\Services\AdminEmailService();
            $emailService->sendUserConfirmation($request->email, 'contact', [
                'name' => $fullName,
                'subject' => $request->subject
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email confirmation contact: ' . $e->getMessage());
        }

        return back()->with('success', 'Votre message a bien été envoyé, merci de nous avoir contactés.');
    }
}
