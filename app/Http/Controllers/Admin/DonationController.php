<?php

namespace App\Http\Controllers\Admin;

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

            $donations = $query->paginate(25);

            $stats = [
                'total' => Donation::count(),
                'success' => Donation::successful()->count(),
                'pending' => Donation::pending()->count(),
                'failed' => Donation::failed()->count(),
                'total_amount' => Donation::successful()->sum('amount'),
            ];

            Log::info('Accès liste donations Admin', ['user_id' => auth()->id()]);

            return view('admin.donations.index', compact('donations', 'stats'));
        } catch (\Exception $e) {
            Log::error('Erreur chargement donations', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du chargement des donations.');
        }
    }

    public function show($id)
    {
        try {
            $donation = Donation::findOrFail($id);
            Log::info('Affichage donation Admin', ['user_id' => auth()->id(), 'donation_id' => $id]);
            return view('admin.donations.show', compact('donation'));
        } catch (\Exception $e) {
            Log::error('Erreur affichage donation', ['error' => $e->getMessage()]);
            return redirect()->route('admin.donations.index')->with('error', 'Donation non trouvée.');
        }
    }

    public function destroy($id)
    {
        try {
            $donation = Donation::findOrFail($id);
            $donation->delete();

            Log::info('Donation supprimée Admin', ['user_id' => auth()->id(), 'donation_id' => $id]);
            return redirect()->route('admin.donations.index')->with('success', 'Donation supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression donation', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
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

            $csv = "ID,Nom,Email,Téléphone,Montant,Devise,Méthode,Statut,Transaction ID,Date\n";
            foreach ($donations as $d) {
                $csv .= implode(',', [
                    $d->id,
                    '"' . str_replace('"', '""', ($d->is_anonymous ? 'Anonyme' : $d->full_name)) . '"',
                    '"' . ($d->is_anonymous ? '' : $d->email) . '"',
                    '"' . ($d->is_anonymous ? '' : $d->phone) . '"',
                    $d->amount,
                    $d->currency,
                    $d->payment_method,
                    $d->payment_status,
                    '"' . $d->transaction_id . '"',
                    $d->created_at->format('d/m/Y H:i'),
                ]) . "\n";
            }

            Log::info('Export donations Admin', ['user_id' => auth()->id()]);

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="donations_' . now()->format('Y-m-d') . '.csv"');
        } catch (\Exception $e) {
            Log::error('Erreur export donations', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de l\'export.');
        }
    }
}
