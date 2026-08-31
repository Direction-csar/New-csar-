<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributionEvent;
use App\Models\DistributionPlanning;
use App\Models\DistributionBeneficiary;
use App\Models\DistributionTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DistributionController extends Controller
{
    public function dashboard()
    {
        $events = DistributionEvent::with('plannings')->orderBy('start_date', 'desc')->get();
        $activeEvent = $events->where('status', 'active')->first();
        $totalPlanned = $events->sum(fn($e) => $e->total_planned_kg);
        $totalExecuted = $events->sum(fn($e) => $e->total_executed_kg);
        $totalBeneficiaries = DistributionBeneficiary::count();
        $totalTickets = DistributionTicket::count();
        $totalCollected = DistributionTicket::where('status', 'collected')->count();

        $alerts = $this->getAlerts();

        return view('admin.distribution.dashboard', compact(
            'events', 'activeEvent', 'totalPlanned', 'totalExecuted',
            'totalBeneficiaries', 'totalTickets', 'totalCollected', 'alerts'
        ));
    }

    public function eventsIndex()
    {
        $events = DistributionEvent::withCount('plannings')->orderBy('start_date', 'desc')->paginate(15);
        return view('admin.distribution.events.index', compact('events'));
    }

    public function eventsCreate()
    {
        return view('admin.distribution.events.create');
    }

    public function eventsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'initial_stock_kg' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DistributionEvent::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(6),
            'description' => $request->description,
            'location' => $request->location,
            'initial_stock_kg' => $request->initial_stock_kg,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.distribution.events.index')
            ->with('success', 'Événement créé avec succès.');
    }

    public function eventsShow($id)
    {
        $event = DistributionEvent::with(['plannings' => function ($q) {
            $q->withCount(['beneficiaries', 'tickets']);
        }])->findOrFail($id);

        $stockEvolution = $this->getStockEvolution($event);

        return view('admin.distribution.events.show', compact('event', 'stockEvolution'));
    }

    public function eventsEdit($id)
    {
        $event = DistributionEvent::findOrFail($id);
        return view('admin.distribution.events.edit', compact('event'));
    }

    public function eventsUpdate(Request $request, $id)
    {
        $event = DistributionEvent::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'initial_stock_kg' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $event->update($request->only(['name', 'description', 'location', 'initial_stock_kg', 'start_date', 'end_date']));

        return redirect()->route('admin.distribution.events.index')
            ->with('success', 'Événement mis à jour.');
    }

    public function eventsUpdateStatus(Request $request, $id)
    {
        $event = DistributionEvent::findOrFail($id);
        $request->validate(['status' => 'required|in:draft,active,closed']);
        $event->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    public function planningsIndex()
    {
        $plannings = DistributionPlanning::with('event', 'assignee')
            ->withCount(['beneficiaries', 'tickets'])
            ->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.distribution.plannings.index', compact('plannings'));
    }

    public function planningsCreate()
    {
        $events = DistributionEvent::where('status', '!=', 'closed')->pluck('name', 'id');
        $distributors = User::where('role', 'distributeur')->where('is_active', 1)->pluck('name', 'id');
        return view('admin.distribution.plannings.create', compact('events', 'distributors'));
    }

    public function planningsStore(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:distribution_events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_quota_kg' => 'required|numeric|min:0',
            'expected_beneficiaries' => 'nullable|integer|min:0',
            'distribution_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DistributionPlanning::create($request->only([
            'event_id', 'name', 'description', 'planned_quota_kg',
            'expected_beneficiaries', 'distribution_date', 'location', 'assigned_to'
        ]) + ['status' => 'draft']);

        return redirect()->route('admin.distribution.plannings.index')
            ->with('success', 'Planning créé avec succès.');
    }

    public function planningsShow($id)
    {
        $planning = DistributionPlanning::with(['event', 'assignee', 'beneficiaries.tickets'])
            ->withCount(['beneficiaries', 'tickets'])->findOrFail($id);
        return view('admin.distribution.plannings.show', compact('planning'));
    }

    public function planningsEdit($id)
    {
        $planning = DistributionPlanning::findOrFail($id);
        $events = DistributionEvent::where('status', '!=', 'closed')->pluck('name', 'id');
        $distributors = User::where('role', 'distributeur')->where('is_active', 1)->pluck('name', 'id');
        return view('admin.distribution.plannings.edit', compact('planning', 'events', 'distributors'));
    }

    public function planningsUpdate(Request $request, $id)
    {
        $planning = DistributionPlanning::findOrFail($id);
        $request->validate([
            'event_id' => 'required|exists:distribution_events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_quota_kg' => 'required|numeric|min:0',
            'expected_beneficiaries' => 'nullable|integer|min:0',
            'distribution_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        $planning->update($request->only([
            'event_id', 'name', 'description', 'planned_quota_kg',
            'expected_beneficiaries', 'distribution_date', 'location', 'assigned_to', 'status'
        ]));

        return redirect()->route('admin.distribution.plannings.index')
            ->with('success', 'Planning mis à jour.');
    }

    public function beneficiariesIndex(Request $request)
    {
        $query = DistributionBeneficiary::with('planning.event', 'validator');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('cni', 'like', "%{$s}%");
            });
        }
        if ($request->filled('planning_id')) {
            $query->where('planning_id', $request->planning_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $beneficiaries = $query->orderBy('created_at', 'desc')->paginate(25);
        $plannings = DistributionPlanning::pluck('name', 'id');
        return view('admin.distribution.beneficiaries.index', compact('beneficiaries', 'plannings'));
    }

    public function beneficiariesCreate()
    {
        $plannings = DistributionPlanning::where('status', 'active')->pluck('name', 'id');
        return view('admin.distribution.beneficiaries.create', compact('plannings'));
    }

    public function beneficiariesStore(Request $request)
    {
        $request->validate([
            'planning_id' => 'required|exists:distribution_plannings,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'quantity_kg' => 'required|numeric|min:0',
            'is_vulnerable' => 'boolean',
            'is_pregnant' => 'boolean',
            'is_elderly' => 'boolean',
            'is_disabled' => 'boolean',
        ]);

        $dup = $this->findDuplicate($request->planning_id, $request->phone, $request->cni, $request->full_name);
        if ($dup) {
            return redirect()->back()->withInput()->with('error', "Doublon détecté: {$dup->full_name} ({$dup->phone}) existe déjà dans ce planning.");
        }

        DistributionBeneficiary::create($request->only([
            'planning_id', 'full_name', 'phone', 'cni', 'address', 'category', 'quantity_kg',
            'is_vulnerable', 'is_pregnant', 'is_elderly', 'is_disabled',
        ]) + ['status' => 'pending']);

        return redirect()->route('admin.distribution.beneficiaries.index')
            ->with('success', 'Bénéficiaire ajouté.');
    }

    public function beneficiariesShow($id)
    {
        $beneficiary = DistributionBeneficiary::with('planning.event', 'tickets.scanner', 'validator')->findOrFail($id);
        return view('admin.distribution.beneficiaries.show', compact('beneficiary'));
    }

    public function beneficiariesEdit($id)
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);
        $plannings = DistributionPlanning::where('status', 'active')->pluck('name', 'id');
        return view('admin.distribution.beneficiaries.edit', compact('beneficiary', 'plannings'));
    }

    public function beneficiariesUpdate(Request $request, $id)
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);
        $request->validate([
            'planning_id' => 'required|exists:distribution_plannings,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cni' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'quantity_kg' => 'required|numeric|min:0',
            'is_vulnerable' => 'boolean',
            'is_pregnant' => 'boolean',
            'is_elderly' => 'boolean',
            'is_disabled' => 'boolean',
            'status' => 'required|in:pending,validated,ticket_issued,kit_collected',
        ]);

        $oldQty = (float) $beneficiary->quantity_kg;
        $newQty = (float) $request->quantity_kg;

        $beneficiary->update($request->only([
            'planning_id', 'full_name', 'phone', 'cni', 'address', 'category', 'quantity_kg',
            'is_vulnerable', 'is_pregnant', 'is_elderly', 'is_disabled', 'status',
        ]));

        if ($oldQty !== $newQty) {
            $planning = DistributionPlanning::find($beneficiary->planning_id);
            if ($planning) {
                $planning->decrement('executed_kg', $oldQty);
                $planning->increment('executed_kg', $newQty);
            }
        }

        return redirect()->route('admin.distribution.beneficiaries.index')
            ->with('success', 'Bénéficiaire mis à jour.');
    }

    public function beneficiariesDestroy($id)
    {
        $beneficiary = DistributionBeneficiary::findOrFail($id);
        $oldQty = (float) $beneficiary->quantity_kg;
        $planning = DistributionPlanning::find($beneficiary->planning_id);
        if ($planning && $beneficiary->status !== 'pending') {
            $planning->decrement('executed_kg', $oldQty);
        }
        $beneficiary->tickets()->delete();
        $beneficiary->delete();

        return redirect()->route('admin.distribution.beneficiaries.index')
            ->with('success', 'Bénéficiaire supprimé.');
    }

    public function ticketsIndex(Request $request)
    {
        $query = DistributionTicket::with('beneficiary', 'planning.event', 'scanner');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_code', 'like', "%{$s}%")->orWhere('qr_token', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $tickets = $query->orderBy('issued_at', 'desc')->paginate(25);
        return view('admin.distribution.tickets.index', compact('tickets'));
    }

    public function ticketsShow($id)
    {
        $ticket = DistributionTicket::with('beneficiary.planning.event', 'scanner', 'scanLogs.user')->findOrFail($id);
        return view('admin.distribution.tickets.show', compact('ticket'));
    }

    public function reports(Request $request)
    {
        $eventId = $request->get('event_id');
        $events = DistributionEvent::orderBy('start_date', 'desc')->pluck('name', 'id');
        $event = $eventId ? DistributionEvent::with('plannings')->findOrFail($eventId) : DistributionEvent::with('plannings')->where('status', 'active')->first();

        if ($event) {
            $plannings = $event->plannings()->withCount(['beneficiaries', 'tickets'])->get();
            $stockEvolution = $this->getStockEvolution($event);
            $duplicates = $this->getDuplicates($event);
            $alerts = $this->getAlertsForEvent($event);
        } else {
            $plannings = collect();
            $stockEvolution = [];
            $duplicates = [];
            $alerts = [];
        }

        return view('admin.distribution.reports', compact('events', 'event', 'plannings', 'stockEvolution', 'duplicates', 'alerts'));
    }

    public function exportReport(Request $request)
    {
        $eventId = $request->get('event_id');
        $event = DistributionEvent::with('plannings.beneficiaries', 'plannings.tickets')->findOrFail($eventId);

        $filename = 'rapport_distribution_' . Str::slug($event->name) . '_' . now()->format('Y-m-d') . '.csv';
        $csv = "\xEF\xBB\xBF";
        $csv .= "Rapport de Distribution - {$event->name}\n";
        $csv .= "Stock initial (kg);{$event->initial_stock_kg}\n";
        $csv .= "Total planifie (kg);{$event->total_planned_kg}\n";
        $csv .= "Total execute (kg);{$event->total_executed_kg}\n";
        $csv .= "Stock restant (kg);{$event->remaining_stock_kg}\n";
        $csv .= "Total beneficiaires;{$event->total_beneficiaries}\n";
        $csv .= "Tickets emis;{$event->total_tickets_issued}\n";
        $csv .= "Kits recuperes;{$event->total_tickets_collected}\n\n";
        $csv .= "Planning;Quota planifie (kg);Execute (kg);En cours (kg);Beneficiaires;Tickets emis;Kits recuperes;Taux execution\n";

        foreach ($event->plannings as $p) {
            $csv .= "\"{$p->name}\";{$p->planned_quota_kg};{$p->executed_kg};{$p->in_progress_kg};{$p->beneficiaries_count};{$p->tickets_count};" . $p->tickets()->where('status', 'collected')->count() . ";{$p->execution_rate}%\n";
        }

        return response($csv)->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function alerts()
    {
        $alerts = $this->getAlerts();
        return view('admin.distribution.alerts', compact('alerts'));
    }

    private function getAlerts(): array
    {
        $alerts = [];
        $events = DistributionEvent::where('status', 'active')->get();

        foreach ($events as $event) {
            if ($event->remaining_stock_kg < 0) {
                $alerts[] = ['event' => $event->name, 'level' => 'critical', 'message' => "Stock négatif: {$event->remaining_stock_kg} kg"];
            }
            if ($event->total_planned_kg > $event->initial_stock_kg) {
                $over = $event->total_planned_kg - $event->initial_stock_kg;
                $alerts[] = ['event' => $event->name, 'level' => 'warning', 'message' => "Dépassement de stock: {$over} kg"];
            }
            foreach ($event->plannings as $p) {
                if ($p->execution_rate < 80 && $p->execution_rate > 0) {
                    $alerts[] = ['event' => $event->name, 'level' => 'warning', 'message' => "Planning '{$p->name}' en retard ({$p->execution_rate}%)"];
                }
            }
            $dups = $this->getDuplicates($event);
            foreach ($dups as $dup) {
                $alerts[] = ['event' => $event->name, 'level' => 'warning', 'message' => $dup['message']];
            }
        }
        return $alerts;
    }

    private function getAlertsForEvent($event): array
    {
        $alerts = [];
        if ($event->remaining_stock_kg < 0) {
            $alerts[] = ['level' => 'critical', 'message' => "Stock négatif: {$event->remaining_stock_kg} kg"];
        }
        if ($event->total_planned_kg > $event->initial_stock_kg) {
            $over = $event->total_planned_kg - $event->initial_stock_kg;
            $alerts[] = ['level' => 'warning', 'message' => "Dépassement de stock planifié: {$over} kg"];
        }
        foreach ($event->plannings as $p) {
            if ($p->execution_rate < 80 && $p->execution_rate > 0) {
                $alerts[] = ['level' => 'warning', 'message' => "Planning '{$p->name}' en retard ({$p->execution_rate}%)"];
            }
        }
        return $alerts;
    }

    private function getStockEvolution($event): array
    {
        $evolution = [];
        $remaining = (float) $event->initial_stock_kg;
        $evolution[] = ['label' => 'Stock initial', 'value' => $remaining];

        foreach ($event->plannings as $p) {
            $remaining -= (float) $p->executed_kg;
            $evolution[] = ['label' => $p->name, 'value' => $remaining];
        }
        $evolution[] = ['label' => 'Projection (reste à servir)', 'value' => $remaining - ($event->total_planned_kg - $event->total_executed_kg)];
        return $evolution;
    }

    private function getDuplicates($event): array
    {
        $duplicates = [];
        $planningIds = $event->plannings()->pluck('id');

        $phoneDups = DistributionBeneficiary::whereIn('planning_id', $planningIds)
            ->whereNotNull('phone')->select('phone', DB::raw('count(*) as cnt'))
            ->groupBy('phone')->having('cnt', '>', 1)->get();
        foreach ($phoneDups as $d) {
            $duplicates[] = ['type' => 'phone', 'value' => $d->phone, 'count' => $d->cnt, 'message' => "Doublon téléphone: {$d->phone} ({$d->cnt}x)"];
        }

        $cniDups = DistributionBeneficiary::whereIn('planning_id', $planningIds)
            ->whereNotNull('cni')->select('cni', DB::raw('count(*) as cnt'))
            ->groupBy('cni')->having('cnt', '>', 1)->get();
        foreach ($cniDups as $d) {
            $duplicates[] = ['type' => 'cni', 'value' => $d->cni, 'count' => $d->cnt, 'message' => "Doublon CNI: {$d->cni} ({$d->cnt}x)"];
        }

        return $duplicates;
    }

    private function findDuplicate($planningId, $phone, $cni, $fullName): ?DistributionBeneficiary
    {
        $query = DistributionBeneficiary::where('planning_id', $planningId);
        if ($phone) {
            $match = (clone $query)->where('phone', $phone)->first();
            if ($match) return $match;
        }
        if ($cni) {
            $match = (clone $query)->where('cni', $cni)->first();
            if ($match) return $match;
        }
        if ($fullName) {
            $match = (clone $query)->where('full_name', 'ILIKE', $fullName)->first();
            if ($match) return $match;
        }
        return null;
    }
}
