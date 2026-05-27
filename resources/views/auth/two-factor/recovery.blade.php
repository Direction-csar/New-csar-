@extends($layout ?? 'layouts.auth')

@section('title', 'Codes de récupération 2FA')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Codes de récupération</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Important :</strong> conservez ces codes dans un endroit sûr.
                        Ils permettent de vous connecter si vous perdez l'accès à votre application 2FA.
                        <strong>Ils ne seront affichés qu'une seule fois.</strong>
                    </div>

                    <div class="bg-light p-3 rounded mb-3">
                        <pre class="mb-0 user-select-all"><code>@foreach ($codes as $code){{ $code }}
@endforeach</code></pre>
                    </div>

                    <button onclick="window.print()" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>

                    <a href="{{ route($dashboardRoute ?? 'admin.dashboard') }}" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> J'ai sauvegardé mes codes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
