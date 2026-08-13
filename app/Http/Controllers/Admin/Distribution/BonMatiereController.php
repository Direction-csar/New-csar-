<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\BonMatiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BonMatiereController extends Controller
{
    public function index()
    {
        $bons = BonMatiere::with('planning.campaign', 'beneficiaire', 'ticket')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.distribution.bon_matieres.index', compact('bons'));
    }

    public function show(BonMatiere $bonMatiere)
    {
        $bonMatiere->load('planning.campaign', 'beneficiaire', 'ticket', 'attributedBy', 'deliveredBy');
        return view('admin.distribution.bon_matieres.show', compact('bonMatiere'));
    }

    public function cancel(BonMatiere $bonMatiere)
    {
        if ($bonMatiere->statut === 'annule') {
            return back()->with('error', 'Bon déjà annulé.');
        }

        DB::transaction(function () use ($bonMatiere) {
            $bonMatiere->update([
                'statut' => 'annule',
                'cancelled_at' => now(),
            ]);

            $planning = $bonMatiere->planning;
            $planning->decrement('executed_quota_kg', $bonMatiere->quantite_kg);
            $planning->campaign->decrement('executed_stock_kg', $bonMatiere->quantite_kg);

            if ($ticket = $bonMatiere->ticket) {
                $ticket->update(['used' => true, 'used_at' => now()]);
            }
        });

        return back()->with('success', 'Bon annulé et stock rétabli.');
    }
}
