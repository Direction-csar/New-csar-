<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur RGPD : droits des utilisateurs sur leurs données personnelles.
 *
 * - Article 15 : droit d'accès (consultation)
 * - Article 17 : droit à l'effacement ("droit à l'oubli")
 * - Article 20 : droit à la portabilité (export JSON)
 */
class GdprController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Page d'accueil RGPD : présente les droits et actions disponibles.
     */
    public function index()
    {
        $user = Auth::user();

        return view('public.gdpr.index', [
            'user' => $user,
        ]);
    }

    /**
     * Article 20 RGPD : exporter les données personnelles au format JSON.
     */
    public function exportData(Request $request)
    {
        $user = Auth::user();

        // Recharger l'utilisateur avec ses relations exportables
        $userData = User::with([
            'notifications',
            'messages',
            'demandes',
            'notificationPreferences',
        ])->find($user->id);

        $export = [
            'export_date'  => now()->toIso8601String(),
            'export_type'  => 'RGPD - Article 20 (portabilité)',
            'user_profile' => [
                'id'              => $userData->id,
                'name'            => $userData->name,
                'email'           => $userData->email,
                'phone'           => $userData->phone,
                'role'            => $userData->role,
                'status'          => $userData->status,
                'position'        => $userData->position,
                'department'      => $userData->department,
                'address'         => $userData->address,
                'created_at'      => $userData->created_at?->toIso8601String(),
                'last_login_at'   => $userData->last_login_at?->toIso8601String(),
                'two_factor'      => (bool) ($userData->two_factor_enabled ?? false),
            ],
            'notifications' => $userData->notifications->map(function ($n) {
                return [
                    'title'      => $n->title ?? null,
                    'message'    => $n->message ?? null,
                    'created_at' => $n->created_at?->toIso8601String(),
                    'read_at'    => $n->read_at?->toIso8601String(),
                ];
            }),
            'messages' => $userData->messages->map(function ($m) {
                return [
                    'subject'    => $m->subject ?? null,
                    'content'    => $m->content ?? null,
                    'created_at' => $m->created_at?->toIso8601String(),
                ];
            }),
            'public_requests' => $userData->demandes->map(function ($d) {
                return [
                    'type'        => $d->type ?? null,
                    'description' => $d->description ?? null,
                    'status'      => $d->status ?? null,
                    'created_at'  => $d->created_at?->toIso8601String(),
                ];
            }),
            'notification_preferences' => $userData->notificationPreferences,
        ];

        Log::info('Export RGPD effectué', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        $filename = 'donnees-csar-' . $user->id . '-' . now()->format('Y-m-d-His') . '.json';

        return response()
            ->json($export, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Article 17 RGPD : supprimer définitivement le compte ("droit à l'oubli").
     *
     * Demande mot de passe + confirmation explicite.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password'     => ['required', 'string'],
            'confirmation' => ['required', 'string', 'in:SUPPRIMER MON COMPTE'],
        ], [
            'password.required'     => 'Mot de passe requis.',
            'confirmation.in'       => 'Veuillez taper exactement « SUPPRIMER MON COMPTE » pour confirmer.',
            'confirmation.required' => 'Confirmation requise.',
        ]);

        $user = Auth::user();

        // Vérifier le mot de passe
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        // Empêcher la suppression de comptes admin (sécurité métier)
        if (in_array($user->role, ['admin', 'dg', 'drh'], true)) {
            return back()->withErrors([
                'confirmation' => 'Les comptes administrateurs ne peuvent pas être supprimés via cette interface. Contactez le DPO à dpo@csar.sn.',
            ]);
        }

        $userId    = $user->id;
        $userEmail = $user->email;

        try {
            DB::transaction(function () use ($user) {
                // Anonymiser/supprimer les données associées
                // (les FK avec onDelete cascade s'occuperont du reste)
                DB::table('users')->where('id', $user->id)->update([
                    'name'     => 'Utilisateur supprimé',
                    'email'    => 'deleted-' . $user->id . '@csar.local',
                    'phone'    => null,
                    'address'  => null,
                    'avatar'   => null,
                    'position' => null,
                    'department' => null,
                ]);

                // Supprimer définitivement
                $user->delete();
            });

            // Déconnecter l'utilisateur
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::warning('Compte utilisateur supprimé (RGPD Art. 17)', [
                'user_id' => $userId,
                'email'   => $userEmail,
                'ip'      => $request->ip(),
            ]);

            return redirect('/')->with('success',
                'Votre compte a été supprimé définitivement. Toutes vos données personnelles ont été effacées conformément au RGPD.'
            );
        } catch (\Exception $e) {
            Log::error('Erreur suppression compte RGPD', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return back()->withErrors([
                'confirmation' => 'Une erreur est survenue. Contactez support@csar.sn.',
            ]);
        }
    }
}
