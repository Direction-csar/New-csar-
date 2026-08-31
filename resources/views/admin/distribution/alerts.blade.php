@extends('layouts.admin')

@section('title', 'Alertes')
@section('page-title', 'Alertes de distribution')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">⚠️ Alertes de distribution</h1>
                    <a href="{{ route('admin.distribution.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                @if(empty($alerts))
                <p class="text-muted text-center py-4 mb-0">Aucune alerte active. Tout est sous contrôle ✅</p>
                @else
                <table class="table table-sm">
                    <thead><tr><th>Niveau</th><th>Événement</th><th>Message</th></tr></thead>
                    <tbody>
                        @foreach($alerts as $alert)
                        <tr>
                            <td><span class="badge {{ $alert['level'] === 'critical' ? 'bg-danger' : 'bg-warning' }}">{{ strtoupper($alert['level']) }}</span></td>
                            <td>{{ $alert['event'] }}</td>
                            <td>{{ $alert['message'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
