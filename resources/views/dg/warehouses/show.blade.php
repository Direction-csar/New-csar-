@extends('layouts.dg-modern')

@section('title', 'Détails de l\'Entrepôt')
@section('page-title', 'Détails de l\'Entrepôt - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-warehouse me-2 text-primary"></i>
                            Détails de l'Entrepôt
                        </h1>
                        <p class="text-muted mb-0 small">
                            Consultation détaillée - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dg.warehouses.index') }}" class="btn btn-outline-primary btn-modern btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Informations Générales
                </h6>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nom de l'Entrepôt</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-building me-2"></i>{{ $warehouse->name ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Statut</label>
                        <div class="p-2">
                            @if($warehouse->is_active)
                                <span class="badge bg-success fs-6">Actif</span>
                            @else
                                <span class="badge bg-danger fs-6">Inactif</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Capacité</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-chart-bar me-2"></i>{{ number_format($warehouse->capacity ?? 0, 0, ',', ' ') }} unités
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Stock Actuel</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-boxes me-2"></i>{{ number_format($warehouse->current_stock ?? 0, 0, ',', ' ') }} unités
                        </div>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <div class="p-2 bg-light rounded" style="min-height: 80px;">
                            <i class="fas fa-align-left me-2"></i>{{ $warehouse->description ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-map-marker-alt me-2 text-success"></i>
                    Localisation
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Adresse</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-home me-2"></i>{{ $warehouse->address ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Ville</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-city me-2"></i>{{ $warehouse->city ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Région</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-globe me-2"></i>{{ $warehouse->region ?? 'N/A' }}
                    </div>
                </div>
                
                @if($warehouse->latitude && $warehouse->longitude)
                <div class="mb-3">
                    <label class="form-label fw-bold">Coordonnées GPS</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-map-pin me-2"></i>
                        <small>{{ $warehouse->latitude }}, {{ $warehouse->longitude }}</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Contact et communication -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-phone me-2 text-info"></i>
                    Contact
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Téléphone</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-phone me-2"></i>{{ $warehouse->phone ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-envelope me-2"></i>{{ $warehouse->email ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-calendar me-2 text-warning"></i>
                    Dates
                </h6>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Création</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-plus me-2"></i>{{ \Carbon\Carbon::parse($warehouse->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Dernière Mise à Jour</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-edit me-2"></i>{{ \Carbon\Carbon::parse($warehouse->updated_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques et performance -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Statistiques et Performance
                </h6>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                            <h5 class="mb-1">{{ $warehouse->stocks_count ?? 0 }}</h5>
                            <small class="text-muted">Articles en Stock</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-percentage fa-2x text-success mb-2"></i>
                            <h5 class="mb-1">
                                @php
                                    $occupationRate = $warehouse->capacity > 0 ? round(($warehouse->current_stock / $warehouse->capacity) * 100, 1) : 0;
                                @endphp
                                {{ $occupationRate }}%
                            </h5>
                            <small class="text-muted">Taux d'Occupation</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                            <h5 class="mb-1">{{ number_format($warehouse->capacity - $warehouse->current_stock, 0, ',', ' ') }}</h5>
                            <small class="text-muted">Capacité Disponible</small>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <h5 class="mb-1">
                                @php
                                    $lowStockItems = $warehouse->stocks ? $warehouse->stocks->where('quantity', '<=', 'min_quantity')->count() : 0;
                                @endphp
                                {{ $lowStockItems }}
                            </h5>
                            <small class="text-muted">Alertes Stock Faible</small>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression de l'occupation -->
                <div class="mt-3">
                    <label class="form-label fw-bold">Occupation de l'Entrepôt</label>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar 
                            @if($occupationRate >= 90) bg-danger
                            @elseif($occupationRate >= 70) bg-warning
                            @else bg-success
                            @endif" 
                            role="progressbar" 
                            style="width: {{ $occupationRate }}%">
                            {{ $occupationRate }}% ({{ number_format($warehouse->current_stock, 0, ',', ' ') }}/{{ number_format($warehouse->capacity, 0, ',', ' ') }})
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des stocks -->
    @if($warehouse->stocks && $warehouse->stocks->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Articles en Stock
                </h6>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Quantité</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouse->stocks as $stock)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-3d me-2" style="width: 35px; height: 35px; background: var(--gradient-info);">
                                            <i class="fas fa-box" style="font-size: 14px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $stock->item_name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $stock->description ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $stock->quantity ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $stock->min_quantity ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $stock->max_quantity ?? 0 }}</span>
                                </td>
                                <td>
                                    @if($stock->quantity <= $stock->min_quantity)
                                        <span class="badge bg-danger">Stock Faible</span>
                                    @elseif($stock->quantity >= $stock->max_quantity)
                                        <span class="badge bg-warning">Stock Élevé</span>
                                    @else
                                        <span class="badge bg-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('dg.stocks.show', $stock->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="text-center py-4">
                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun article en stock</h5>
                    <p class="text-muted">Cet entrepôt ne contient actuellement aucun article.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection





































