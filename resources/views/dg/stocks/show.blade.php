@extends('layouts.dg-modern')

@section('title', 'Détails du Stock')
@section('page-title', 'Détails du Stock - Vue DG')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">
                            <i class="fas fa-boxes me-2 text-primary"></i>
                            Détails du Stock
                        </h1>
                        <p class="text-muted mb-0 small">
                            Consultation détaillée - {{ now()->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dg.stocks.index') }}" class="btn btn-outline-primary btn-modern btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du stock -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Informations Générales
                </h6>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nom de l'Article</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-tag me-2"></i>{{ $stock->item_name ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Type de Stock</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-layer-group me-2"></i>{{ $stock->stock_type_id ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Quantité Actuelle</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-cubes me-2"></i>{{ $stock->quantity ?? 'N/A' }} unités
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Quantité Minimale</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $stock->min_quantity ?? 'N/A' }} unités
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Quantité Maximale</label>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-arrow-up me-2"></i>{{ $stock->max_quantity ?? 'N/A' }} unités
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Statut</label>
                        <div class="p-2">
                            @if($stock->is_active)
                                <span class="badge bg-success fs-6">Actif</span>
                            @else
                                <span class="badge bg-danger fs-6">Inactif</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($stock->description)
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <div class="p-2 bg-light rounded" style="min-height: 80px;">
                            <i class="fas fa-align-left me-2"></i>{{ $stock->description }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-warehouse me-2 text-success"></i>
                    Informations Entrepôt
                </h6>
                
                @if($stock->warehouse)
                <div class="mb-3">
                    <label class="form-label fw-bold">Entrepôt</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-building me-2"></i>{{ $stock->warehouse->name ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Adresse</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-map-marker-alt me-2"></i>{{ $stock->warehouse->address ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Région</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-globe me-2"></i>{{ $stock->warehouse->region ?? 'N/A' }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Capacité</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-chart-bar me-2"></i>{{ $stock->warehouse->capacity ?? 'N/A' }} unités
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Aucun entrepôt associé
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Informations supplémentaires -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-calendar me-2 text-info"></i>
                    Dates et Période
                </h6>
                
                @if($stock->expiry_date)
                <div class="mb-3">
                    <label class="form-label fw-bold">Date d'Expiration</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-times me-2"></i>{{ \Carbon\Carbon::parse($stock->expiry_date)->format('d/m/Y') }}
                    </div>
                </div>
                @endif
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Date de Création</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-plus me-2"></i>{{ \Carbon\Carbon::parse($stock->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Dernière Mise à Jour</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calendar-edit me-2"></i>{{ \Carbon\Carbon::parse($stock->updated_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-info me-2 text-warning"></i>
                    Informations Supplémentaires
                </h6>
                
                @if($stock->batch_number)
                <div class="mb-3">
                    <label class="form-label fw-bold">Numéro de Lot</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-barcode me-2"></i>{{ $stock->batch_number }}
                    </div>
                </div>
                @endif
                
                @if($stock->supplier)
                <div class="mb-3">
                    <label class="form-label fw-bold">Fournisseur</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-truck me-2"></i>{{ $stock->supplier }}
                    </div>
                </div>
                @endif
                
                @if($stock->unit_price)
                <div class="mb-3">
                    <label class="form-label fw-bold">Prix Unitaire</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-money-bill me-2"></i>{{ number_format($stock->unit_price, 0, ',', ' ') }} FCFA
                    </div>
                </div>
                @endif
                
                @if($stock->total_value)
                <div class="mb-3">
                    <label class="form-label fw-bold">Valeur Totale</label>
                    <div class="p-2 bg-light rounded">
                        <i class="fas fa-calculator me-2"></i>{{ number_format($stock->total_value, 0, ',', ' ') }} FCFA
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Alertes et statut -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="mb-3 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                    Alertes et Statut
                </h6>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Niveau de Stock</label>
                        <div class="p-2">
                            @if($stock->quantity <= $stock->min_quantity)
                                <span class="badge bg-danger fs-6">Stock Faible</span>
                            @elseif($stock->quantity >= $stock->max_quantity)
                                <span class="badge bg-warning fs-6">Stock Élevé</span>
                            @else
                                <span class="badge bg-success fs-6">Stock Normal</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Pourcentage de Stock</label>
                        <div class="p-2 bg-light rounded">
                            @php
                                $percentage = $stock->max_quantity > 0 ? round(($stock->quantity / $stock->max_quantity) * 100, 1) : 0;
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar 
                                    @if($percentage <= 20) bg-danger
                                    @elseif($percentage <= 50) bg-warning
                                    @else bg-success
                                    @endif" 
                                    role="progressbar" 
                                    style="width: {{ $percentage }}%">
                                    {{ $percentage }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Actions Recommandées</label>
                        <div class="p-2">
                            @if($stock->quantity <= $stock->min_quantity)
                                <span class="badge bg-danger fs-6">Réapprovisionner</span>
                            @elseif($stock->quantity >= $stock->max_quantity)
                                <span class="badge bg-warning fs-6">Surveiller</span>
                            @else
                                <span class="badge bg-success fs-6">Aucune</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



































