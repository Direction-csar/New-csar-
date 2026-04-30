@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Nouveau Rapport SIM')
@section('page-title', 'Créer un Rapport SIM')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nouveau Rapport SIM</h1>
    <p class="page-subtitle">Créer un nouveau rapport SIM</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2"></i>Créer un Rapport SIM
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sim-reports.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du Rapport *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type de Rapport *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Sélectionner un type</option>
                                <option value="mensuel">Mensuel</option>
                                <option value="trimestriel">Trimestriel</option>
                                <option value="annuel">Annuel</option>
                                <option value="personnalise">Personnalisé</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="format" class="form-label">Format de Sortie *</label>
                            <select class="form-select" id="format" name="format" required>
                                <option value="">Sélectionner un format</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_debut" class="form-label">Date de Début *</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="date_fin" class="form-label">Date de Fin *</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sections à Inclure *</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="section_demandes" name="sections[]" value="demandes" checked>
                                    <label class="form-check-label" for="section_demandes">
                                        Demandes
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="section_stocks" name="sections[]" value="stocks" checked>
                                    <label class="form-check-label" for="section_stocks">
                                        Stocks
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="section_entrepots" name="sections[]" value="entrepots" checked>
                                    <label class="form-check-label" for="section_entrepots">
                                        Entrepôts
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="section_personnel" name="sections[]" value="personnel">
                                    <label class="form-check-label" for="section_personnel">
                                        Personnel
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="section_financier" name="sections[]" value="financier">
                                    <label class="form-check-label" for="section_financier">
                                        Financier
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="section_statistiques" name="sections[]" value="statistiques" checked>
                                    <label class="form-check-label" for="section_statistiques">
                                        Statistiques
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <button type="submit" name="action" value="generate" class="btn btn-success">
                            <i class="fas fa-magic me-2"></i>Générer
                        </button>
                        <a href="{{ route('admin.sim-reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Informations
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong>Types de rapports :</strong>
                </p>
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-calendar-day me-2"></i>Mensuel</li>
                    <li><i class="fas fa-calendar-week me-2"></i>Trimestriel</li>
                    <li><i class="fas fa-calendar me-2"></i>Annuel</li>
                    <li><i class="fas fa-cog me-2"></i>Personnalisé</li>
                </ul>
                
                <hr>
                
                <p class="text-muted">
                    <strong>Formats disponibles :</strong>
                </p>
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-file-pdf me-2"></i>PDF (Recommandé)</li>
                    <li><i class="fas fa-file-excel me-2"></i>Excel</li>
                    <li><i class="fas fa-file-csv me-2"></i>CSV</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection