@extends('layouts.public')

@section('title', __('pages.faq'))
@section('meta_description', 'FAQ du CSAR : demande d\'assistance, suivi de demande, partenariats, rapports et données pour usagers et bailleurs de fonds.')
@section('meta_keywords', 'CSAR, FAQ, sécurité alimentaire, assistance, partenaires, rapports, Sénégal')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff;">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-3">{{ __('messages.faq.title') }}</h1>
        <p class="lead mb-0">{{ __('messages.faq.subtitle') }}</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="h4 mb-4">{{ __('messages.faq.section_usagers') }}</h2>
        <div class="accordion mb-5" id="accordionUsagers">
            @foreach($faqUsagers as $i => $item)
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#usager-{{ $i }}" aria-expanded="{{ $i === 0 }}">
                        {{ $item['q'] }}
                    </button>
                </h3>
                <div id="usager-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#accordionUsagers">
                    <div class="accordion-body">{{ $item['r'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <h2 class="h4 mb-4">{{ __('messages.faq.section_bailleurs') }}</h2>
        <div class="accordion" id="accordionBailleurs">
            @foreach($faqBailleurs as $i => $item)
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bailleur-{{ $i }}">
                        {{ $item['q'] }}
                    </button>
                </h3>
                <div id="bailleur-{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#accordionBailleurs">
                    <div class="accordion-body">{{ $item['r'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 p-4 bg-light rounded">
            <p class="mb-2"><strong>{{ __('messages.faq.not_found') }}</strong></p>
            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="btn btn-success me-2">{{ __('messages.nav.contact_us') }}</a>
            <a href="{{ route('partners.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-success">{{ __('messages.nav.become_partner') }}</a>
        </div>
    </div>
</section>
@endsection
