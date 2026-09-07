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
        $report = $activeEvent ? $this->buildReport($activeEvent) : null;

        return view('admin.distribution.dashboard', compact(
            'events', 'activeEvent', 'totalPlanned', 'totalExecuted',
            'totalBeneficiaries', 'totalTickets', 'totalCollected', 'alerts', 'report'
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
            $report = $this->buildReport($event);
        } else {
            $plannings = collect();
            $stockEvolution = [];
            $duplicates = [];
            $alerts = [];
            $report = null;
        }

        return view('admin.distribution.reports', compact('events', 'event', 'plannings', 'stockEvolution', 'duplicates', 'alerts', 'report'));
    }

    public function printReport(Request $request)
    {
        $event = DistributionEvent::with('plannings')->findOrFail($request->get('event_id'));
        $report = $this->buildReport($event);
        return view('admin.distribution.reports_print', compact('event', 'report'));
    }

    private function buildReport(DistributionEvent $event): array
    {
        $plannings = $event->plannings()->withCount(['beneficiaries', 'tickets'])->orderBy('id')->get();
        $planningIds = $plannings->pluck('id');

        $initial = (float) $event->initial_stock_kg;
        $planned = (float) $plannings->sum('planned_quota_kg');
        $executed = (float) $plannings->sum('executed_kg');
        $inProgress = max(0, $planned - $executed);
        $remaining = $initial - $executed;
        $overrun = $planned - $initial;
        $projected = $initial - $planned;
        $executionRate = $planned > 0 ? round($executed / $planned * 100, 1) : 0;
        $consumptionRate = $initial > 0 ? round($executed / $initial * 100, 1) : 0;
        $marginPct = $initial > 0 ? round(($initial - $planned) / $initial * 100, 1) : 0;

        $stockStatus = $projected < 0 ? 'CRITIQUE' : ($consumptionRate >= 80 ? 'A SURVEILLER' : 'OK');

        $rows = [];
        $lateCount = 0;
        foreach ($plannings as $p) {
            $rate = (float) $p->execution_rate;
            if ($rate >= 100) {
                $alert = 'OK';
            } elseif ($rate >= 80) {
                $alert = 'A SURVEILLER';
            } else {
                $alert = 'RETARD';
                $lateCount++;
            }
            $ticketsActive = $p->tickets()->whereIn('status', ['issued', 'scanned', 'collected'])->count();
            $rows[] = [
                'id' => $p->id,
                'name' => $p->name,
                'planned' => (float) $p->planned_quota_kg,
                'executed' => (float) $p->executed_kg,
                'in_progress' => max(0, (float) $p->planned_quota_kg - (float) $p->executed_kg),
                'beneficiaries' => $p->beneficiaries_count,
                'tickets' => $ticketsActive,
                'collected' => $p->tickets()->where('status', 'collected')->count(),
                'rate' => $rate,
                'alert' => $alert,
            ];
        }

        $totalBeneficiaries = DistributionBeneficiary::whereIn('planning_id', $planningIds)->count();
        $totalTickets = DistributionTicket::whereIn('planning_id', $planningIds)->whereIn('status', ['issued', 'scanned', 'collected'])->count();
        $totalCollected = DistributionTicket::whereIn('planning_id', $planningIds)->where('status', 'collected')->count();
        $withoutTicket = DistributionBeneficiary::whereIn('planning_id', $planningIds)->whereIn('status', ['pending', 'validated'])->count();
        $ticketNotCollected = $totalTickets - $totalCollected;

        $phoneDups = DistributionBeneficiary::whereIn('planning_id', $planningIds)->whereNotNull('phone')->where('phone', '!=', '')
            ->select('phone', DB::raw('count(*) as cnt'))->groupBy('phone')->having('cnt', '>', 1)->get();
        $cniDups = DistributionBeneficiary::whereIn('planning_id', $planningIds)->whereNotNull('cni')->where('cni', '!=', '')
            ->select('cni', DB::raw('count(*) as cnt'))->groupBy('cni')->having('cnt', '>', 1)->get();
        $nameDups = DistributionBeneficiary::whereIn('planning_id', $planningIds)
            ->select(DB::raw('LOWER(full_name) as fn'), DB::raw('count(*) as cnt'))->groupBy('fn')->having('cnt', '>', 1)->get();

        $dupPhoneCount = (int) $phoneDups->sum(fn ($d) => $d->cnt - 1);
        $dupCniCount = (int) $cniDups->sum(fn ($d) => $d->cnt - 1);
        $dupNameCount = (int) $nameDups->sum(fn ($d) => $d->cnt - 1);

        $fmt = fn ($v) => number_format($v, 0, ',', ' ');

        $controls = [
            ['label' => 'Couverture du stock par le planifie', 'value' => ($projected < 0 ? '-' : '+') . $fmt(abs($projected)) . ' kg', 'status' => $projected < 0 ? 'ALERTE' : 'OK'],
            ['label' => 'Niveau de consommation du stock', 'value' => str_replace('.', ',', (string) $consumptionRate) . ' %', 'status' => $consumptionRate >= 90 ? 'ALERTE' : ($consumptionRate >= 80 ? 'EN COURS' : 'OK')],
            ['label' => 'Beneficiaires sans ticket', 'value' => $fmt($withoutTicket), 'status' => $withoutTicket > 0 ? 'ALERTE' : 'OK'],
            ['label' => 'Tickets emis non recuperes (don non servi)', 'value' => $fmt($ticketNotCollected), 'status' => $ticketNotCollected > 0 ? 'EN COURS' : 'OK'],
            ['label' => "Plannings en retard d'execution (<80 %)", 'value' => $lateCount, 'status' => $lateCount > 0 ? 'ALERTE' : 'OK'],
            ['label' => 'Volume global en cours (non servi)', 'value' => $fmt($inProgress) . ' kg', 'status' => $inProgress > 0 ? 'EN COURS' : 'OK'],
            ['label' => 'Marge de stock non planifiee', 'value' => str_replace('.', ',', (string) $marginPct) . ' %', 'status' => $marginPct < 0 ? 'ALERTE' : ($marginPct < 5 ? 'EN COURS' : 'OK')],
            ['label' => 'Doublons de numero de telephone', 'value' => $dupPhoneCount, 'status' => $dupPhoneCount > 0 ? 'ALERTE' : 'OK'],
            ['label' => 'Doublons de CNI', 'value' => $dupCniCount, 'status' => $dupCniCount > 0 ? 'ALERTE' : 'OK'],
            ['label' => "Autres doublons d'identite (nom complet)", 'value' => $dupNameCount, 'status' => $dupNameCount > 0 ? 'ALERTE' : 'OK'],
        ];

        $alertCount = count(array_filter($controls, fn ($c) => $c['status'] === 'ALERTE'));
        $inProgressCount = count(array_filter($controls, fn ($c) => $c['status'] === 'EN COURS'));

        $stockEvolution = [['label' => 'Stock initial', 'value' => $initial]];
        $r = $initial;
        foreach ($rows as $row) {
            $r -= $row['executed'];
            $stockEvolution[] = ['label' => $row['name'], 'value' => $r];
        }
        $stockEvolution[] = ['label' => 'Projection apres reste a servir', 'value' => $projected];

        $completed = array_values(array_filter($rows, fn ($x) => $x['alert'] === 'OK'));
        $late = array_values(array_filter($rows, fn ($x) => $x['alert'] === 'RETARD'));
        $watch = array_values(array_filter($rows, fn ($x) => $x['alert'] === 'A SURVEILLER'));

        $listNames = fn (array $items) => implode(', ', array_map(fn ($x) => $x['name'] . ($x['alert'] !== 'OK' ? ' (' . str_replace('.', ',', (string) $x['rate']) . ' %)' : ''), $items));

        $summary = "Le present compte rendu presente la situation consolidee des operations de distribution « {$event->name} »"
            . ($event->location ? " ({$event->location})" : '')
            . " du Commissariat a la Securite Alimentaire et a la Resilience (CSAR), arretee au " . now()->format('d/m/Y') . ". "
            . "Sur un stock initial disponible de " . $fmt($initial) . " kg (" . str_replace('.', ',', (string) round($initial / 1000, 1)) . " tonnes), le quota total planifie sur l'ensemble des " . count($rows) . " planning(s) atteint " . $fmt($planned) . " kg"
            . ($overrun > 0
                ? ", soit un depassement de " . $fmt($overrun) . " kg (" . str_replace('.', ',', (string) $marginPct) . " %) par rapport a la dotation disponible. "
                : ", soit une marge non planifiee de " . $fmt(-$overrun) . " kg (" . str_replace('.', ',', (string) $marginPct) . " %). ")
            . $fmt($executed) . " kg ont deja ete executes / servis (taux d'execution global de " . str_replace('.', ',', (string) $executionRate) . " %), et " . $fmt($inProgress) . " kg restent en cours de distribution. "
            . "Le stock physique restant s'etablit a " . $fmt($remaining) . " kg, pour un taux de consommation du stock de " . str_replace('.', ',', (string) $consumptionRate) . " %. "
            . "Le statut du stock est declare {$stockStatus}"
            . ($projected < 0 ? " : si les " . $fmt($inProgress) . " kg encore en cours sont integralement distribues, le deficit projete atteindra " . $fmt($projected) . " kg." : ".");

        $planningNarrative = count($rows) . " planning(s) composent le consolide. "
            . $fmt($totalBeneficiaries) . " beneficiaires sont recenses au total pour " . $fmt($totalTickets) . " ticket(s) delivre(s), soit " . $fmt($withoutTicket) . " beneficiaire(s) en attente de ticket ; "
            . $fmt($totalCollected) . " don(s) ont ete effectivement recuperes et " . $fmt($ticketNotCollected) . " ticket(s) restent non recuperes. "
            . ($lateCount > 0
                ? $lateCount . " planning(s) affichent un taux d'execution inferieur au seuil de vigilance de 80 % : " . $listNames($late) . "."
                : "Aucun planning n'est sous le seuil de vigilance de 80 %.");

        $totalDups = $dupPhoneCount + $dupCniCount + $dupNameCount;
        $alertsNarrative = "Les controles automatiques du tableau de bord font apparaitre {$alertCount} alerte(s) active(s) et {$inProgressCount} signal(aux) « en cours ». "
            . ($totalDups > 0
                ? "Le controle qualite des donnees revele {$dupPhoneCount} doublon(s) de numero de telephone, {$dupCniCount} doublon(s) de CNI et {$dupNameCount} autre(s) doublon(s) d'identite. Ces anomalies doivent etre purgees avant toute cloture definitive du consolide" . ($overrun > 0 ? " : elles peuvent expliquer une partie du depassement apparent de " . $fmt($overrun) . " kg si des quotas ont ete comptabilises en double." : '.')
                : "Aucun doublon d'identification n'a ete detecte.");

        $stockNarrative = "Les sorties cumulees depuis le debut des operations atteignent " . $fmt($executed) . " kg, laissant un stock physique disponible de " . $fmt($remaining) . " kg. "
            . ($projected < 0
                ? "Si l'integralite des " . $fmt($inProgress) . " kg restant a servir est distribuee sans ajustement du plan, le stock deviendra negatif (" . $fmt($projected) . " kg, soit " . str_replace('.', ',', (string) round($projected / 1000, 2)) . " tonnes), confirmant l'alerte de depassement et le statut CRITIQUE du stock."
                : "Apres distribution integrale du reste a servir (" . $fmt($inProgress) . " kg), le stock residuel projete s'etablira a " . $fmt($projected) . " kg.");

        $conclusion = "Les operations de distribution enregistrent un taux global d'execution de " . str_replace('.', ',', (string) $executionRate) . " %. "
            . ($projected < 0
                ? "La situation du stock est critique : le quota planifie depasse la dotation initiale de " . $fmt($initial) . " kg, avec un deficit projete de " . $fmt(abs($projected)) . " kg si le reste a servir est integralement distribue. "
                : "La dotation initiale couvre l'integralite du quota planifie. ")
            . (count($completed) > 0 ? "Les plannings " . $listNames($completed) . " sont entierement executes. " : '')
            . (count($late) > 0 ? "Une attention prioritaire doit etre portee aux plannings " . $listNames($late) . ". " : '')
            . (count($watch) > 0 ? "Les plannings " . $listNames($watch) . " sont a surveiller. " : '')
            . ($withoutTicket > 0 ? "Le traitement des " . $fmt($withoutTicket) . " beneficiaire(s) sans ticket" . ($totalDups > 0 ? " et des doublons detectes" : '') . " doit etre finalise avant toute cloture. " : '')
            . ($ticketNotCollected > 0 ? $fmt($ticketNotCollected) . " beneficiaire(s) ont retire leur ticket sans recuperer leur don." : '');

        return [
            'generated_at' => now(),
            'initial' => $initial,
            'planned' => $planned,
            'executed' => $executed,
            'in_progress' => $inProgress,
            'remaining' => $remaining,
            'overrun' => $overrun,
            'projected' => $projected,
            'execution_rate' => $executionRate,
            'consumption_rate' => $consumptionRate,
            'margin_pct' => $marginPct,
            'stock_status' => $stockStatus,
            'rows' => $rows,
            'total_beneficiaries' => $totalBeneficiaries,
            'total_tickets' => $totalTickets,
            'total_collected' => $totalCollected,
            'without_ticket' => $withoutTicket,
            'ticket_not_collected' => $ticketNotCollected,
            'late_count' => $lateCount,
            'controls' => $controls,
            'alert_count' => $alertCount,
            'dup_phone' => $dupPhoneCount,
            'dup_cni' => $dupCniCount,
            'dup_name' => $dupNameCount,
            'stock_evolution' => $stockEvolution,
            'donut' => [
                'executed' => $executed,
                'covered' => max(0, min($remaining, $inProgress)),
                'overrun' => max(0, $overrun),
            ],
            'text' => [
                'summary' => $summary,
                'plannings' => $planningNarrative,
                'alerts' => $alertsNarrative,
                'stock' => $stockNarrative,
                'conclusion' => $conclusion,
            ],
        ];
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
