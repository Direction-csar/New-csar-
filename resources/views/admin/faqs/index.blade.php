@extends('layouts.admin')

@section('title', 'Gestion FAQ')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-question-circle me-2"></i>Gestion FAQ</h1>
            <p class="text-muted">Questions fréquentes pour usagers et bailleurs</p>
        </div>
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouvelle question
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-list fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Publiées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['published'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Usagers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['usager'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Bailleurs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['bailleur'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-handshake fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Toutes les questions</h6></div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Question</th>
                            <th>Catégorie</th>
                            <th>Langue</th>
                            <th>Position</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faqs as $faq)
                        <tr>
                            <td><strong>{{ Str::limit($faq->question, 60) }}</strong></td>
                            <td><span class="badge badge-info">{{ $faq->category }}</span></td>
                            <td><span class="badge badge-secondary">{{ $faq->locale }}</span></td>
                            <td>{{ $faq->position }}</td>
                            <td>
                                @if($faq->is_published)
                                    <span class="badge badge-success">Publiée</span>
                                @else
                                    <span class="badge badge-warning">Brouillon</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form action="{{ route('admin.faqs.toggle-published', $faq->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $faq->is_published ? 'btn-warning' : 'btn-success' }}" title="{{ $faq->is_published ? 'Dépublier' : 'Publier' }}">
                                            <i class="fas {{ $faq->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Supprimer cette FAQ ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune FAQ trouvée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $faqs->links() }}</div>
        </div>
    </div>
</div>
@endsection
