<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('bonMatiere.planning.campaign', 'bonMatiere.beneficiaire')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.distribution.tickets.index', compact('tickets'));
    }

    public function scan()
    {
        return view('admin.distribution.tickets.scan');
    }

    public function processScan(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $ticket = Ticket::where('code', $validated['code'])->with('bonMatiere.planning')->first();

        if (!$ticket) {
            return back()->with('error', 'Ticket non trouvé.');
        }

        if ($ticket->used) {
            return back()->with('error', 'Ticket déjà utilisé.');
        }

        $bon = $ticket->bonMatiere;

        DB::transaction(function () use ($ticket, $bon) {
            $ticket->update([
                'used' => true,
                'used_at' => now(),
                'used_by' => Auth::id(),
            ]);

            $bon->update([
                'statut' => 'livre',
                'delivered_at' => now(),
                'delivered_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Retrait validé pour ' . $bon->beneficiaire->name . ' (' . $bon->quantite_kg . ' kg).');
    }

    public function reissue(Ticket $ticket, Request $request)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        do {
            $code = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
        } while (Ticket::where('code', $code)->exists());

        $ticket->update([
            'code' => $code,
            'used' => false,
            'used_at' => null,
            'used_by' => null,
            'reissued_at' => now(),
            'reissued_by' => Auth::id(),
            'reissue_reason' => $request->input('reason'),
            'qr_data' => json_encode([
                'bon_id' => $ticket->bon_matiere_id,
                'code' => $code,
            ]),
        ]);

        return back()->with('success', 'Ticket réédité. Nouveau code : ' . $code);
    }
}
