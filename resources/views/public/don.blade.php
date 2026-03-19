@extends('layouts.public')

@section('title', __('pages.don'))
@section('meta_description', __('messages.don.subtitle'))

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #0d3b5c 0%, #1e5a8a 100%); color: #fff;">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-3">{{ __('messages.don.title') }}</h1>
        <p class="lead mb-0">{{ __('messages.don.subtitle') }}</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <h2 class="h3 mb-4" style="color: #0d3b5c;">{{ __('messages.don.section_title') }}</h2>
                <p class="text-muted mb-4">{{ __('messages.don.description') }}</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ __('messages.don.benefit_emergency') }}</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ __('messages.don.benefit_resilience') }}</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ __('messages.don.benefit_vulnerable') }}</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h3 class="h5 mb-4" style="color: #0d3b5c;">{{ __('messages.don.how_title') }}</h3>
                        <p class="text-muted mb-4">{{ __('messages.don.how_desc') }}</p>
                        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}?sujet=don" class="btn btn-danger btn-lg px-4">
                            <i class="fas fa-heart me-2"></i>{{ __('messages.don.contact_btn') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
