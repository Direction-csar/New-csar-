<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CommunicationsController extends Controller
{
    /**
     * Afficher la page des communications
     */
    public function index()
    {
        try {
            // Statistiques des communications
            $stats = $this->getCommunicationStats();

            // Messages récents
            $recentMessages = Schema::hasTable('messages')
                ? Message::orderBy('created_at', 'desc')->limit(10)->get()
                : collect();

            // Notifications récentes
            $recentNotifications = Schema::hasTable('notifications')
                ? Notification::orderBy('created_at', 'desc')->limit(10)->get()
                : collect();

            // Campagnes newsletter récentes (table optionnelle)
            $recentNewsletters = Schema::hasTable('newsletters')
                ? DB::table('newsletters')->orderBy('created_at', 'desc')->limit(10)->get()
                : collect();

            // Abonnés newsletter récents (table optionnelle)
            $recentSubscribers = Schema::hasTable('newsletter_subscribers')
                ? DB::table('newsletter_subscribers')->orderBy('created_at', 'desc')->limit(10)->get()
                : collect();

            // Logs d'audit des communications (table optionnelle)
            $auditLogs = Schema::hasTable('audit_logs')
                ? DB::table('audit_logs')
                    ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                    ->where(function ($q) {
                        $q->where('audit_logs.action', 'LIKE', '%communication%')
                            ->orWhere('audit_logs.action', 'LIKE', '%message%')
                            ->orWhere('audit_logs.action', 'LIKE', '%newsletter%');
                    })
                    ->select('audit_logs.id', 'audit_logs.action', DB::raw('audit_logs.description as details'), 'audit_logs.created_at', DB::raw('users.name as user_name'))
                    ->orderBy('audit_logs.created_at', 'desc')
                    ->limit(10)
                    ->get()
                : collect();

            Log::info('Accès à la page Communications', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);

            $view = request()->routeIs('ctc.*') ? 'ctc.communications.index' : 'admin.communications.index';
            return view($view, compact(
                'stats',
                'recentMessages',
                'recentNotifications',
                'recentNewsletters',
                'recentSubscribers',
                'auditLogs'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la page Communications', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => Carbon::now()
            ]);

            return redirect()->back()->with('error', 'Erreur lors du chargement de la page Communications.');
        }
    }

    /**
     * Obtenir les statistiques des communications
     */
    private function getCommunicationStats()
    {
        $stats = [
            'total_messages' => 0,
            'unread_messages' => 0,
            'today_messages' => 0,
            'week_messages' => 0,
            'total_notifications' => 0,
            'unread_notifications' => 0,
            'today_notifications' => 0,
            'week_notifications' => 0,
            'total_newsletters' => 0,
            'sent_newsletters' => 0,
            'pending_newsletters' => 0,
            'today_newsletters' => 0,
            'total_subscribers' => 0,
            'active_subscribers' => 0,
            'today_subscribers' => 0,
            'week_subscribers' => 0,
            'total_communications' => 0,
            'today_communications' => 0,
        ];

        try {
            if (Schema::hasTable('messages')) {
                $stats['total_messages'] = Message::count();
                $stats['unread_messages'] = Message::where('lu', false)->count();
                $stats['today_messages'] = Message::whereDate('created_at', today())->count();
                $stats['week_messages'] = Message::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            }

            if (Schema::hasTable('notifications')) {
                $stats['total_notifications'] = Notification::count();
                $stats['unread_notifications'] = Notification::where('read', false)->count();
                $stats['today_notifications'] = Notification::whereDate('created_at', today())->count();
                $stats['week_notifications'] = Notification::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            }

            if (Schema::hasTable('newsletters')) {
                $stats['total_newsletters'] = DB::table('newsletters')->count();
                $stats['sent_newsletters'] = DB::table('newsletters')->where('status', 'sent')->count();
                $stats['pending_newsletters'] = DB::table('newsletters')->whereIn('status', ['draft', 'pending', 'scheduled'])->count();
                $stats['today_newsletters'] = DB::table('newsletters')->whereDate('created_at', today())->count();
            }

            if (Schema::hasTable('newsletter_subscribers')) {
                $stats['total_subscribers'] = DB::table('newsletter_subscribers')->count();
                $stats['active_subscribers'] = DB::table('newsletter_subscribers')
                    ->whereIn('status', ['subscribed', 'active'])
                    ->count();
                $stats['today_subscribers'] = DB::table('newsletter_subscribers')->whereDate('created_at', today())->count();
                $stats['week_subscribers'] = DB::table('newsletter_subscribers')
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count();
            }

            $stats['total_communications'] = $stats['total_messages'] + $stats['total_notifications'] + $stats['total_newsletters'];
            $stats['today_communications'] = $stats['today_messages'] + $stats['today_notifications'] + $stats['today_newsletters'];

            return $stats;
        } catch (\Exception $e) {
            Log::error('Erreur dans getCommunicationStats', ['error' => $e->getMessage()]);
            return $stats;
        }
    }

    /**
     * Obtenir les statistiques en temps réel (AJAX)
     */
    public function realtimeStats()
    {
        try {
            $stats = $this->getCommunicationStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => Carbon::now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans realtimeStats', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
}

