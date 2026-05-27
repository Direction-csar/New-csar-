<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Donation::orderBy('created_at', 'desc');

            if ($request->filled('status')) {
                $query->where('payment_status', $request->status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('transaction_id', 'like', "%{$search}%");
                });
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $donations = $query->paginate(25);

            $stats = [
                'total' => Donation::count(),
                'success' => Donation::successful()->count(),
                'pending' => Donation::pending()->count(),
                'failed' => Donation::failed()->count(),
                'total_amount' => Donation::successful()->sum('amount'),
                'monthly_amount' => Donation::successful()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
            ];

            Log::info('Accès liste donations DG', ['user_id' => auth()->id()]);

            return view('dg.donations.index', compact('donations', 'stats'));
        } catch (\Exception $e) {
            Log::error('Erreur chargement donations DG', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du chargement des donations.');
        }
    }

    public function show($id)
    {
        try {
            $donation = Donation::findOrFail($id);
            Log::info('Affichage donation DG', ['user_id' => auth()->id(), 'donation_id' => $id]);
            return view('dg.donations.show', compact('donation'));
        } catch (\Exception $e) {
            Log::error('Erreur affichage donation DG', ['error' => $e->getMessage()]);
            return redirect()->route('dg.donations.index')->with('error', 'Donation non trouvée.');
        }
    }

    public function export(Request $request)
    {
        try {
            $query = Donation::orderBy('created_at', 'desc');

            if ($request->filled('status')) {
                $query->where('payment_status', $request->status);
            }

            $donations = $query->get();

            $csv = "ID,Nom,Email,Téléphone,Montant,Devise,Méthode,Fournisseur,Statut,Transaction ID,Date\n";
            foreach ($donations as $d) {
                $csv .= implode(',', [
                    $d->id,
                    '"' . str_replace('"', '""', ($d->is_anonymous ? 'Anonyme' : $d->full_name)) . '"',
                    '"' . ($d->is_anonymous ? '' : $d->email) . '"',
                    '"' . ($d->is_anonymous ? '' : $d->phone) . '"',
                    $d->amount,
                    $d->currency,
                    $d->payment_method,
                    $d->payment_provider,
                    $d->payment_status,
                    '"' . $d->transaction_id . '"',
                    $d->created_at->format('d/m/Y H:i'),
                ]) . "\n";
            }

            Log::info('Export donations DG', ['user_id' => auth()->id()]);

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="donations_dg_' . now()->format('Y-m-d') . '.csv"');
        } catch (\Exception $e) {
            Log::error('Erreur export donations DG', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de l\'export.');
        }
    }
}
