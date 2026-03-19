@extends('layouts.public')

@section('title', __('pages.search'))
@section('meta_description', __('messages.search.description'))

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff;">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-3"><i class="fas fa-search me-2"></i>{{ __('messages.search.title') }}</h1>
        <p class="lead mb-4">{{ __('messages.search.description') }}</p>
        <form action="{{ route('search.index', ['locale' => app()->getLocale()]) }}" method="get" class="row g-2">
            <div class="col-md-10">
                <input type="search" name="q" value="{{ old('q', $query) }}" class="form-control form-control-lg" placeholder="{{ __('messages.search.placeholder') }}" autofocus>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-light btn-lg w-100"><i class="fas fa-search me-1"></i>{{ __('messages.search.title') }}</button>
            </div>
        </form>
    </div>
</section>

<section class="py-5">
    <div class="container">
        @if(strlen($query) >= 2)
            @if(count($results) > 0)
                <p class="text-muted mb-4">{{ __('messages.search.results_count', ['count' => count($results), 'query' => e($query)]) }}</p>
                <div class="list-group">
                    @foreach($results as $item)
                        <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                            <span class="badge bg-{{ $item['type'] === 'actualite' ? 'primary' : 'success' }} rounded-pill align-self-start mt-1">
                                {{ $item['type'] === 'actualite' ? __('messages.search.type_news') : __('messages.search.type_report') }}
                            </span>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">{{ $item['title'] }}</h5>
                                @if($item['excerpt'])
                                    <p class="mb-1 text-muted small">{{ $item['excerpt'] }}</p>
                                @endif
                                @if($item['date'])
                                    <small class="text-muted">{{ $item['date'] }}</small>
                                @endif
                            </div>
                            <i class="fas fa-chevron-right text-muted align-self-center"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>{{ __('messages.search.no_results') }}</h4>
                    <p class="text-muted">{{ __('messages.search.no_results_desc', ['query' => e($query)]) }}</p>
                </div>
            @endif
        @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-search fa-4x mb-3 opacity-50"></i>
                <p>{{ __('messages.search.min_chars') }}</p>
            </div>
        @endif
    </div>
</section>
@endsection
