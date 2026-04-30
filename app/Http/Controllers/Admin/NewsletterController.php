<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewsletterController extends Controller
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
     * Afficher la page de newsletter
     */
    public function index(Request $request)
    {
        try {
            // Statistiques depuis la base MySQL
            $stats = $this->getNewsletterStats();
            
            // Récupérer les abonnés depuis la base MySQL
            $query = \App\Models\NewsletterSubscriber::query();
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $subscribers = $query->paginate(15);
            
            return view('admin.newsletter.index', compact('subscribers', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@index', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return view('admin.newsletter.index', [
                'subscribers' => collect(),
                'stats' => $this->getDefaultStats()
            ]);
        }
    }
    
    // Les méthodes createNewsletter(), addSubscriber() et createTemplate() ont été supprimées
    // Les newsletters et abonnés sont créés automatiquement depuis la plateforme publique
    
    /**
     * Envoyer une newsletter
     */
    public function sendNewsletter($id)
    {
        try {
            // Simulation d'envoi de newsletter
            $newsletter = [
                'id' => $id,
                'status' => 'sent',
                'sent_at' => now(),
                'updated_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Newsletter envoyée',
                "La newsletter a été envoyée avec succès",
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Newsletter envoyée avec succès',
                'data' => $newsletter
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@sendNewsletter', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'newsletter_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la newsletter'
            ], 500);
        }
    }
    
    /**
     * Supprimer une newsletter
     */
    public function destroyNewsletter($id)
    {
        try {
            // Simulation de suppression
            $newsletterTitle = 'Newsletter supprimée';
            
            // Créer une notification
            $this->createNotification(
                'Newsletter supprimée',
                "La newsletter '{$newsletterTitle}' a été supprimée",
                'warning'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Newsletter supprimée avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@destroyNewsletter', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'newsletter_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la newsletter'
            ], 500);
        }
    }
    
    /**
     * Désabonner un utilisateur
     */
    public function unsubscribe($id)
    {
        try {
            // Simulation de désabonnement
            $subscriber = [
                'id' => $id,
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
                'updated_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Abonné désabonné',
                "L'abonné a été désabonné avec succès",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Abonné désabonné avec succès',
                'data' => $subscriber
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@unsubscribe', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'subscriber_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du désabonnement'
            ], 500);
        }
    }
    
    /**
     * Exporter les abonnés
     */
    public function exportSubscribers(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            
            // Simulation d'export
            $subscribers = []; // Liste des abonnés
            
            if ($format === 'csv') {
                return $this->exportToCsv($subscribers);
            } elseif ($format === 'excel') {
                return $this->exportToExcel($subscribers);
            } else {
                return $this->exportToCsv($subscribers);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@exportSubscribers', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des abonnés'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques de newsletter
     */
    public function getStats()
    {
        try {
            $stats = $this->getNewsletterStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@getStats', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
    
    /**
     * Obtenir les analytiques
     */
    public function getAnalytics(Request $request)
    {
        try {
            $period = $request->get('period', 'month');
            
            // Simulation d'analytiques
            $analytics = [
                'subscribers_evolution' => [
                    'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                    'data' => [0, 0, 0, 0, 0, 0]
                ],
                'newsletter_performance' => [
                    'labels' => ['Ouvert', 'Non ouvert', 'Clics'],
                    'data' => [0, 0, 0]
                ],
                'open_rates' => [
                    'average' => 0,
                    'trend' => 'stable'
                ],
                'click_rates' => [
                    'average' => 0,
                    'trend' => 'stable'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans NewsletterController@getAnalytics', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des analytiques'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques de newsletter depuis la base MySQL
     */
    private function getNewsletterStats()
    {
        try {
            return [
                'total_subscribers' => \App\Models\NewsletterSubscriber::count(),
                'active_subscribers' => \App\Models\NewsletterSubscriber::whereIn('status', ['subscribed', 'active'])->count(),
                'inactive_subscribers' => \App\Models\NewsletterSubscriber::where('status', 'unsubscribed')->count(),
                'total_newsletters' => \App\Models\Newsletter::count(),
                'sent_newsletters' => \App\Models\Newsletter::whereIn('status', ['sent', 'draft'])->count(),
                'pending_newsletters' => \App\Models\Newsletter::where('status', 'pending')->count(),
                'today_subscribers' => \App\Models\NewsletterSubscriber::whereDate('created_at', today())->count(),
                'this_week_subscribers' => \App\Models\NewsletterSubscriber::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans getNewsletterStats: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Statistiques par défaut
     */
    private function getDefaultStats()
    {
        return [
            'total_subscribers' => 0,
            'active_subscribers' => 0,
            'inactive_subscribers' => 0,
            'unsubscribed' => 0
        ];
    }
    
    /**
     * Exporter vers CSV
     */
    private function exportToCsv($subscribers)
    {
        $filename = 'abonnes_newsletter_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($subscribers) {
            $file = fopen('php://output', 'w');
            
            // En-têtes
            fputcsv($file, ['Nom', 'Email', 'Statut', 'Date d\'abonnement']);
            
            // Données
            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber['name'] ?? '',
                    $subscriber['email'] ?? '',
                    ucfirst($subscriber['status'] ?? ''),
                    $subscriber['subscribed_at'] ?? ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Exporter vers Excel (simulation)
     */
    private function exportToExcel($subscribers)
    {
        // Simulation d'export Excel
        return $this->exportToCsv($subscribers);
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