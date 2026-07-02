@extends('layouts.admin')

@section('title', 'Logs Archives')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-history text-info"></i> Logs d'accès aux archives</h2>
        <a href="{{ route('admin.archives.index') }}" class="btn btn-outline-dark">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Document</th>
                        <th>Action</th>
                        <th>IP</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td>
                                <code class="small">{{ $log->archive?->reference ?? '—' }}</code>
                                <div class="small text-muted">{{ $log->archive?->title }}</div>
                            </td>
                            <td>
                                @switch($log->action)
                                    @case('view')
                                        <span class="badge bg-info"><i class="fas fa-eye"></i> Consultation</span>
                                        @break
                                    @case('download')
                                        <span class="badge bg-secondary"><i class="fas fa-download"></i> Téléchargement</span>
                                        @break
                                    @case('print')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-print"></i> Impression</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ $log->action }}</span>
                                @endswitch
                            </td>
                            <td><code class="small">{{ $log->ip_address ?? '—' }}</code></td>
                            <td>
                                @if($log->meta && isset($log->meta['pages']))
                                    <small class="text-muted">Pages: <strong>{{ $log->meta['pages'] }}</strong></small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun log trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
