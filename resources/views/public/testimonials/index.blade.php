@extends('layouts.public')

@section('title', 'Témoignages - CSAR')
@section('meta_description', 'Découvrez les témoignages de nos partenaires, bénéficiaires et collaborateurs sur les missions du CSAR.')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h1 class="display-5 fw-bold" style="color: #166534;">Témoignages</h1>
            <p class="lead text-muted">Ce que nos partenaires et bénéficiaires disent de nous</p>
            <a href="{{ route('testimonials.create') }}" class="btn btn-success btn-lg mt-3" style="border-radius: 50px; padding: 12px 36px;">
                <i class="fas fa-pen me-2"></i>Partager mon témoignage
            </a>
        </div>

        @if($testimonials->count() > 0)
        <div class="row g-4">
            @foreach($testimonials as $t)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 20px; transition: transform 0.3s;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; font-size: 1.4rem; font-weight: 700;">
                                    {{ strtoupper(substr($t->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0 fw-bold" style="color: #166534;">{{ $t->name }}</h5>
                                @if($t->organization)
                                <small class="text-muted">{{ $t->organization }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= ($t->rating ?? 5))
                                <i class="fas fa-star text-warning"></i>
                                @else
                                <i class="far fa-star text-muted"></i>
                                @endif
                            @endfor
                        </div>

                        <p class="card-text text-muted" style="font-style: italic;">
                            "{{ Str::limit($t->message, 200) }}"
                        </p>

                        @if($t->type === 'mission' && ($t->mission_location || $t->mission_date))
                        <div class="mt-3 p-2 rounded" style="background: #f0fdf4;">
                            <small class="text-success">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $t->mission_location ?? 'Mission' }}
                                @if($t->mission_date)
                                &middot; {{ $t->mission_date->format('d/m/Y') }}
                                @endif
                            </small>
                        </div>
                        @endif

                        <div class="mt-3 text-end">
                            <small class="text-muted">{{ $t->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $testimonials->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-comment-dots" style="font-size: 4rem; color: #9ca3af;"></i>
            <h3 class="mt-3 text-muted">Aucun témoignage pour le moment</h3>
            <p class="text-muted">Soyez le premier à partager votre expérience !</p>
            <a href="{{ route('testimonials.create') }}" class="btn btn-success" style="border-radius: 50px;">
                <i class="fas fa-pen me-2"></i>Partager mon témoignage
            </a>
        </div>
        @endif
    </div>
</section>
@endsection
