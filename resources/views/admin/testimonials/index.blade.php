@extends('layouts.admin')

@section('title', 'Gestion des témoignages')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Témoignages</h1>
            <p class="text-muted mb-0">Modération des témoignages publics</p>
        </div>
        <a href="{{ route('testimonials.index') }}" target="_blank" class="btn btn-outline-success">
            <i class="fas fa-eye me-1"></i>Voir la page publique
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #f0fdf4;">
                        <i class="fas fa-comments text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">Total</h6>
                        <h4 class="mb-0 fw-bold">{{ $testimonials->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #fffbeb;">
                        <i class="fas fa-clock text-warning fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">En attente</h6>
                        <h4 class="mb-0 fw-bold">{{ $pendingCount }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #ecfdf5;">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">Approuvés</h6>
                        <h4 class="mb-0 fw-bold">{{ $testimonials->total() - $pendingCount }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #eff6ff;">
                        <i class="fas fa-star text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted">En vedette</h6>
                        <h4 class="mb-0 fw-bold">{{ $testimonials->where('is_featured', true)->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow" style="border-radius: 20px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Auteur</th>
                            <th>Note</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($testimonials as $t)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; font-weight: 700;">
                                        {{ strtoupper(substr($t->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $t->name }}</div>
                                        @if($t->organization)
                                        <small class="text-muted">{{ $t->organization }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= ($t->rating ?? 0))
                                    <i class="fas fa-star text-warning" style="font-size: 0.75rem;"></i>
                                    @else
                                    <i class="far fa-star text-muted" style="font-size: 0.75rem;"></i>
                                    @endif
                                @endfor
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.9rem;">{{ Str::limit($t->message, 60) }}</span>
                            </td>
                            <td>
                                @if($t->type === 'mission')
                                <span class="badge bg-info">Mission</span>
                                @else
                                <span class="badge bg-secondary">Général</span>
                                @endif
                            </td>
                            <td>
                                @if($t->status === 'approved')
                                <span class="badge bg-success">Approuvé</span>
                                @elseif($t->status === 'pending')
                                <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                <span class="badge bg-danger">Rejeté</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $t->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.testimonials.show', $t->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($t->status === 'pending')
                                    <form action="{{ route('admin.testimonials.approve', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.testimonials.reject', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.testimonials.toggle-featured', $t->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $t->is_featured ? 'btn-warning' : 'btn-outline-warning' }}" title="{{ $t->is_featured ? 'Retirer' : 'Mettre en vedette' }}">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.testimonials.destroy', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce témoignage ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fs-2 mb-3 d-block"></i>
                                Aucun témoignage trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($testimonials->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $testimonials->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
