@extends('layouts.admin')

@section('title', 'SIM — Demandes d\'accès')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-key me-2"></i>Demandes d'accès aux données SIM</h1>
        <a href="{{ route('admin.sim.dashboard') }}" class="btn btn-outline-secondary">Tableau de bord SIM</a>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <form method="get" class="d-flex gap-2">
                <select name="status" class="form-select" style="max-width:200px">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvé</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    <option value="restricted" {{ request('status') === 'restricted' ? 'selected' : '' }}>Restreint</option>
                </select>
                <button type="submit" class="btn btn-outline-primary">Filtrer</button>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Demandeur</th>
                        <th>Organisation</th>
                        <th>Sujet</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                        <tr>
                            <td>{{ $r->created_at?->format('d/m/Y') }}</td>
                            <td>{{ $r->requester_name }}</td>
                            <td>{{ $r->organization }}</td>
                            <td>{{ Str::limit($r->request_subject, 40) }}</td>
                            <td>
                                @php
                                    $badge = match($r->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'restricted' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $r->status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.sim.access-requests.show', $r) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection
