<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Get the current authenticated user ID based on the guard
     */
    private function getCurrentUserId()
    {
        return auth('ctc')->check() ? auth('ctc')->id() : auth()->id();
    }

    /**
     * Get the correct route prefix based on current request
     */
    private function getRoutePrefix()
    {
        return request()->routeIs('ctc.*') ? 'ctc' : 'admin';
    }

    /**
     * Afficher la liste des messages
     */
    public function index(Request $request)
    {
        try {
            // Statistiques
            $stats = $this->getMessageStats();
            
            // Filtres
            $query = \App\Models\Message::query();
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('sujet', 'like', "%{$search}%")
                      ->orWhere('contenu', 'like', "%{$search}%")
                      ->orWhere('expediteur', 'like', "%{$search}%")
                      ->orWhere('email_expediteur', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('status')) {
                $query->where('lu', $request->status === 'read');
            }
            
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }
            
            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $messages = $query->paginate(15);
            
            return view('admin.messages.index', compact('messages', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans MessageController@index', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return view('admin.messages.index', [
                'messages' => collect(),
                'stats' => $this->getDefaultStats()
            ]);
        }
    }
    
    // Les méthodes create() et store() ont été supprimées
    // Les messages sont créés automatiquement depuis la plateforme publique
    
    /**
     * Afficher les détails d'un message
     */
    public function show($id)
    {
        try {
            // Récupérer le message depuis la base de données MySQL
            $message = \App\Models\Message::findOrFail($id);
            
            // Marquer comme lu si ce n'est pas déjà fait
            if (!$message->lu) {
                $message->update([
                    'lu' => true,
                    'lu_at' => now()
                ]);
            }
            
            return view('admin.messages.show', compact('message'));
        } catch (\Exception $e) {
            Log::error('Erreur dans MessageController@show', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'message_id' => $id
            ]);
            
            return redirect()->route('admin.messages.index')
                ->with('error', 'Message non trouvé');
        }
    }
    
    // Les méthodes edit() et update() ont été supprimées
    // Les messages ne peuvent pas être modifiés depuis l'admin
    
    /**
     * Supprimer un message
     */
    public function destroy($id)
    {
        try {
            // Récupérer le message depuis la base de données
            $message = \App\Models\Message::findOrFail($id);
            $messageSender = $message->sender_name ?? 'Expéditeur inconnu';
            
            // Supprimer le message de la base de données
            $message->delete();
            
            // Créer une notification
            $this->createNotification(
                'Message supprimé',
                "Le message de {$messageSender} a été supprimé avec succès",
                'warning'
            );
            
            // Log de l'action
            Log::info('Message supprimé', [
                'message_id' => $id,
                'sender' => $messageSender,
                'user_id' => $this->getCurrentUserId(),
                'user_name' => auth()->user()->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message supprimé avec succès'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans MessageController@destroy', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'message_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du message'
            ], 500);
        }
    }
    
    /**
     * Marquer un message comme lu
     */
    public function markAsRead($id)
    {
        try {
            // Récupérer le message depuis la base de données MySQL
            $message = \App\Models\Message::findOrFail($id);
            
            // Marquer comme lu
            $message->update([
                'lu' => true,
                'lu_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message marqué comme lu',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans MessageController@markAsRead', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'message_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage du message'
            ], 500);
        }
    }
    
    /**
     * Marquer tous les messages comme lus
     */
    public function markAllAsRead()
    {
        try {
            // Marquer tous les messages non lus comme lus dans la base MySQL
            $count = \App\Models\Message::where('lu', false)->update([
                'lu' => true,
                'lu_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "{$count} message(s) marqué(s) comme lu(s)"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans MessageController@markAllAsRead', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage des messages'
            ], 500);
        }
    }
    
    /**
     * Répondre à un message
     */
    public function reply(Request $request, $id)
    {
        try {
            $request->validate([
                'reply_subject' => 'required|string|max:255',
                'reply_content' => 'required|string'
            ]);
            
            // Récupérer le message depuis la base de données MySQL
            $message = \App\Models\Message::findOrFail($id);
            
            // Ajouter la réponse
            $message->update([
                'reponse' => $request->reply_content,
                'reponse_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Réponse envoyée avec succès',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans MessageController@reply', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'message_id' => $id,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la réponse'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques des messages
     */
    private function getMessageStats()
    {
        try {
            return [
                'total_messages' => \App\Models\Message::count(),
                'unread_messages' => \App\Models\Message::where('lu', false)->count(),
                'replied_messages' => \App\Models\Message::whereNotNull('reponse')->count(),
                'pending_replies' => \App\Models\Message::where('lu', true)->whereNull('reponse')->count()
            ];
        } catch (\Exception $e) {
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Statistiques par défaut
     */
    private function getDefaultStats()
    {
        return [
            'total_messages' => 0,
            'unread_messages' => 0,
            'replied_messages' => 0,
            'pending_replies' => 0
        ];
    }
    
    /**
     * Créer une notification
     */
    private function createNotification($title, $message, $type = 'info')
    {
        try {
            if (class_exists('App\Models\Notification')) {
                \App\Models\Notification::create([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'user_id' => $this->getCurrentUserId(),
                    'read' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de notification', [
                'error' => $e->getMessage()
            ]);
        }
    }
}