@extends('layouts.admin')

@section('title', 'Événements de distribution')
@section('page-title', 'Événements de distribution')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">📅 Événements de distribution</h1>
                    <a href="{{ route('admin.distribution.events.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nouvel événement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Nom</th><th>Lieu</th><th>Stock initial (kg)</th><th>Plannings</th><th>Période</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                        <tr>
                            <td><a href="{{ route('admin.distribution.events.show', $event->id) }}">{{ $event->name }}</a></td>
                            <td>{{ $event->location ?? '—' }}</td>
                            <td>{{ number_format($event->initial_stock_kg, 0, ',', ' ') }}</td>
                            <td>{{ $event->plannings_count }}</td>
                            <td>{{ optional($event->start_date)->format('d/m/Y') }} — {{ optional($event->end_date)->format('d/m/Y') ?? '...' }}</td>
                            <td>
                                @if($event->status === 'active') <span class="badge bg-success">Actif</span>
                                @elseif($event->status === 'draft') <span class="badge bg-secondary">Brouillon</span>
                                @else <span class="badge bg-danger">Clôturé</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.distribution.events.show', $event->id) }}" class="btn btn-outline-info btn-sm py-0 px-2"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.distribution.events.edit', $event->id) }}" class="btn btn-outline-warning btn-sm py-0 px-2"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $events->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
