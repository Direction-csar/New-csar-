@extends('layouts.admin')

@section('title', 'Témoignage de ' . $testimonial->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Retour à la liste
                </a>
                <div class="btn-group">
                    @if($testimonial->status === 'pending')
                    <form action="{{ route('admin.testimonials.approve', $testimonial->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Approuver
                        </button>
                    </form>
                    <form action="{{ route('admin.testimonials.reject', $testimonial->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-times me-1"></i>Rejeter
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.testimonials.toggle-featured', $testimonial->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn {{ $testimonial->is_featured ? 'btn-warning' : 'btn-outline-warning' }}">
                            <i class="fas fa-star me-1"></i>{{ $testimonial->is_featured ? 'Retirer' : 'Vedette' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow" style="border-radius: 24px;">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; font-size: 1.8rem; font-weight: 700;">
                            {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                        </div>
                        <div class="ms-4">
                            <h2 class="mb-1 fw-bold">{{ $testimonial->name }}</h2>
                            @if($testimonial->organization)
                            <p class="text-muted mb-0">{{ $testimonial->organization }}</p>
                            @endif
                            <div class="mt-2">
                                @if($testimonial->status === 'approved')
                                <span class="badge bg-success">Approuvé</span>
                                @elseif($testimonial->status === 'pending')
                                <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                <span class="badge bg-danger">Rejeté</span>
                                @endif
                                @if($testimonial->is_featured)
                                <span class="badge bg-warning"><i class="fas fa-star me-1"></i>Vedette</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Email</h6>
                            <p class="fw-semibold">{{ $testimonial->email ?? 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Type</h6>
                            <p class="fw-semibold">{{ $testimonial->type === 'mission' ? 'Mission sur le terrain' : 'Général' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Note</h6>
                            <p>
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= ($testimonial->rating ?? 0))
                                    <i class="fas fa-star text-warning fs-5"></i>
                                    @else
                                    <i class="far fa-star text-muted fs-5"></i>
                                    @endif
                                @endfor
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Date de soumission</h6>
                            <p class="fw-semibold">{{ $testimonial->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($testimonial->type === 'mission')
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Lieu de mission</h6>
                            <p class="fw-semibold">{{ $testimonial->mission_location ?? 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Date de mission</h6>
                            <p class="fw-semibold">{{ $testimonial->mission_date ? $testimonial->mission_date->format('d/m/Y') : 'Non renseignée' }}</p>
                        </div>
                        @endif
                    </div>

                    <hr class="my-4">

                    <h6 class="text-muted mb-3">Message</h6>
                    <div class="p-4 rounded" style="background: #f8fafc;">
                        <p class="mb-0" style="font-size: 1.1rem; line-height: 1.8;">"{{ $testimonial->message }}"</p>
                    </div>

                    <div class="mt-4 text-end">
                        <form action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce témoignage ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
