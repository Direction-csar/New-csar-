@extends('layouts.admin')

@section('title', 'Plannings de distribution')
@section('page-title', 'Plannings de distribution')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📋 Plannings de distribution</h1>
                    <a href="{{ route('admin.distribution.plannings.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouveau planning</a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success small py-1">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Planning</th><th>Événement</th><th>Quota (kg)</th><th>Exécuté (kg)</th><th>En cours</th><th>Bénéf.</th><th>Tickets</th><th>Taux</th><th>Distributeur</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($plannings as $p)
                        <tr>
                            <td><a href="{{ route('admin.distribution.plannings.show', $p->id) }}"><strong>{{ $p->name }}</strong></a></td>
                            <td>{{ $p->event?->name ?? '—' }}</td>
                            <td>{{ number_format($p->planned_quota_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($p->executed_kg, 0, ',', ' ') }}</td>
                            <td>{{ number_format($p->in_progress_kg, 0, ',', ' ') }}</td>
                            <td>{{ $p->beneficiaries_count }}</td>
                            <td>{{ $p->tickets_count }}</td>
                            <td><span class="badge {{ $p->execution_rate >= 80 ? 'bg-success' : ($p->execution_rate > 0 ? 'bg-warning' : 'bg-danger') }}">{{ $p->execution_rate }}%</span></td>
                            <td>{{ $p->assignee?->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $p->status }}</span></td>
                            <td>
                                <a href="{{ route('admin.distribution.plannings.show', $p->id) }}" class="btn btn-outline-info btn-sm py-0 px-2"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.distribution.plannings.edit', $p->id) }}" class="btn btn-outline-warning btn-sm py-0 px-2"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="text-center text-muted py-3">Aucun planning enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $plannings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
