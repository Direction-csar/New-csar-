@extends('layouts.direction-portal')

@section('title', 'Dossier ' . $demande->tracking_code . ' - DTL')
@section('page-title', 'Dossier ' . $demande->tracking_code)

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Dossier</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted" style="width:40%">Code</th><td><code>{{ $demande->tracking_code }}</code></td></tr>
                    <tr><th class="text-muted">Demandeur</th><td>{{ $demande->full_name ?? $demande->name }}</td></tr>
                    <tr><th class="text-muted">Type</th><td>{{ $demande->type }}</td></tr>
                    <tr><th class="text-muted">Région</th><td>{{ $demande->region }}</td></tr>
                    <tr><th class="text-muted">Objet</th><td>{{ $demande->subject }}</td></tr>
                    <tr><th class="text-muted">Statut</th><td>{!! $demande->workflow_status_badge !!}</td></tr>
                    <tr><th class="text-muted">Validée DG par</th>
                        <td>{{ $demande->dgApprover?->name ?? '-' }}
                            @if($demande->dg_approved_at)<br><small class="text-muted">{{ \Carbon\Carbon::parse($demande->dg_approved_at)->format('d/m/Y H:i') }}</small>@endif
                        </td></tr>
                    <tr><th class="text-muted">Transmise DTL le</th>
                        <td>{{ $demande->dtl_transmitted_at ? \Carbon\Carbon::parse($demande->dtl_transmitted_at)->format('d/m/Y H:i') : '-' }}</td></tr>
                </table>
                @if($demande->description)
                <hr><p class="small mb-0">{{ $demande->description }}</p>@endif
                @if($demande->dg_signature_file)
                <hr><a href="{{ asset('storage/' . $demande->dg_signature_file) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-signature me-1"></i>Document signé
                </a>@endif
                @if($demande->scan_file)
                <a href="{{ asset('storage/' . $demande->scan_file) }}" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>Scan
                </a>@endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><i class="fas fa-pen me-2 text-success"></i>Instruction opérationnelle</div>
            <div class="card-body">
                <form method="POST" action="{{ route('dtl.demandes.instruct', $demande->id) }}">
                    @csrf
                    <textarea name="instructions" class="form-control mb-2" rows="3" required
                        placeholder="Instruction officielle du DTL pour ce dossier...">{{ $demande->dtl_instructions }}</textarea>
                    <button class="btn btn-success btn-sm"><i class="fas fa-save me-1"></i>Enregistrer l'instruction</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><i class="fas fa-tasks me-2 text-warning"></i>Affecter une mission</div>
            <div class="card-body">
                @if($demande->workflow_status === 'approuvee')
                <form method="POST" action="{{ route('dtl.demandes.missions.store', $demande->id) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Type de mission</label>
                        <select name="type" class="form-select form-select-sm" id="missionType" required>
                            <option value="inspection">Inspection terrain (inspecteur)</option>
                            <option value="stock_exit">Sortie de stock (magasinier)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Affecter à</label>
                        <select name="assigned_to" class="form-select form-select-sm" required>
                            <optgroup label="Inspecteurs" id="group-inspecteur">
                                @foreach($inspecteurs as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </optgroup>
                            <optgroup label="Magasiniers" id="group-magasinier">
                                @foreach($magasiniers as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Instructions</label>
                        <textarea name="instructions" class="form-control form-control-sm" rows="3" required
                            placeholder="Instructions détaillées de la mission..."></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Créer la mission</button>
                </form>
                @else
                    <div class="alert alert-warning mb-0 small">
                        Le dossier doit être au statut « Approuvée » pour recevoir des missions (statut actuel : {{ $demande->workflow_status_label }}).
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fas fa-list me-2 text-info"></i>Missions ({{ $demande->missions->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Type</th><th>Affectée à</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    @forelse($demande->missions as $mission)
                        <tr>
                            <td>{{ $mission->id }}</td>
                            <td>{{ $mission->type_label }}</td>
                            <td>{{ $mission->assignee?->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ $mission->status === 'terminee' ? 'success' : ($mission->status === 'annulee' ? 'dark' : ($mission->status === 'rapport_soumis' ? 'info' : 'warning text-dark')) }}">{{ $mission->status_label }}</span></td>
                            <td>
                                @if($mission->status === 'rapport_soumis')
                                <form method="POST" action="{{ route('dtl.missions.validate', $mission->id) }}" class="d-inline"
                                      onsubmit="return confirm('Valider ce rapport et clôturer la mission ?')">
                                    @csrf
                                    <button class="btn btn-outline-success btn-sm" title="Valider le rapport"><i class="fas fa-check"></i></button>
                                </form>
                                <button class="btn btn-outline-warning btn-sm" title="Demander une correction"
                                        data-bs-toggle="collapse" data-bs-target="#correct-{{ $mission->id }}"><i class="fas fa-undo"></i></button>
                                @endif
                                @if(!in_array($mission->status, ['terminee','annulee']))
                                <form method="POST" action="{{ route('dtl.missions.cancel', $mission->id) }}" class="d-inline"
                                      onsubmit="return confirm('Annuler cette mission ?')">
                                    @csrf
                                    <input type="hidden" name="reason" value="Annulée par le DTL">
                                    <button class="btn btn-outline-danger btn-sm" title="Annuler"><i class="fas fa-times"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @if($mission->status === 'rapport_soumis')
                        <tr class="collapse" id="correct-{{ $mission->id }}">
                            <td colspan="5" class="bg-light">
                                <form method="POST" action="{{ route('dtl.missions.correct', $mission->id) }}" class="d-flex gap-2 align-items-start p-2">
                                    @csrf
                                    <textarea name="review_comment" class="form-control form-control-sm" rows="1" required
                                              placeholder="Correction demandée à l'agent..."></textarea>
                                    <button class="btn btn-warning btn-sm text-nowrap">Demander correction</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @if($mission->report)
                        <tr><td colspan="5" class="small text-muted bg-light">
                            <strong>Rapport :</strong> {{ Str::limit($mission->report, 200) }}
                            @if($mission->visited_at)<br><small>Visite : {{ $mission->visited_at->format('d/m/Y H:i') }}</small>@endif
                            @if($mission->review_comment)<br><small class="text-warning"><strong>Revue DTL :</strong> {{ $mission->review_comment }}</small>@endif
                        </td></tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-tasks d-block mb-2 opacity-50"></i>
                                <small>Aucune mission créée pour ce dossier.</small>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
