@extends('layouts.admin')

@section('title', 'Traiter la Demande')
@section('page-title', 'Traiter la Demande')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">✏️ Traiter la Demande</h1>
                        <p class="text-muted mb-0 small">
                            Code de suivi: {{ $demande->tracking_code ?? 'CSAR-' . $demande->id }}
                            <span class="ms-2">{!! $demande->workflow_status_badge !!}</span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.demandes.show', $demande->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Voir
                        </a>
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Progress Bar -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3 bg-light">
                <h6 class="fw-bold mb-3 text-center"><i class="fas fa-stream me-2 text-primary"></i>Workflow de la demande</h6>
                @php
                    $workflowSteps = [
                        'soumise' => ['label' => 'Soumise', 'icon' => 'fa-paper-plane', 'color' => 'secondary'],
                        'en_revue' => ['label' => 'En revue', 'icon' => 'fa-eye', 'color' => 'info'],
                        'document_attente' => ['label' => 'Attente doc', 'icon' => 'fa-file-alt', 'color' => 'warning'],
                        'signee' => ['label' => 'Signée', 'icon' => 'fa-signature', 'color' => 'primary'],
                        'scannee' => ['label' => 'Scannée', 'icon' => 'fa-file-pdf', 'color' => 'info'],
                        'validee_dg' => ['label' => 'Validée DG', 'icon' => 'fa-stamp', 'color' => 'success'],
                        'approuvee' => ['label' => 'Approuvée', 'icon' => 'fa-check-circle', 'color' => 'success'],
                        'rejetee' => ['label' => 'Rejetée', 'icon' => 'fa-times-circle', 'color' => 'danger'],
                        'cloturee' => ['label' => 'Clôturée', 'icon' => 'fa-archive', 'color' => 'dark'],
                    ];
                    $currentWf = $demande->workflow_status ?? 'soumise';
                    $wfKeys = array_keys($workflowSteps);
                    $currentIndex = array_search($currentWf, $wfKeys);
                    if ($currentIndex === false) $currentIndex = 0;
                @endphp
                <div class="d-flex justify-content-between align-items-center position-relative" style="gap: 4px;">
                    @foreach($workflowSteps as $key => $step)
                        @php
                            $stepIndex = array_search($key, $wfKeys);
                            $isCompleted = $stepIndex < $currentIndex && $currentWf !== 'rejetee';
                            $isCurrent = $key === $currentWf;
                            $isRejected = $currentWf === 'rejetee' && $key === 'rejetee';
                        @endphp
                        <div class="text-center flex-fill" style="position: relative; z-index: 2;">
                            <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle mb-1"
                                 style="width: 36px; height: 36px; font-size: 0.75rem;
                                 background: {{ $isCompleted || $isCurrent || $isRejected ? 'linear-gradient(135deg, var(--bs-' . $step['color'] . '), var(--bs-' . $step['color'] . '-dark, var(--bs-' . $step['color'] . ')))' : '#e2e8f0' }};
                                 color: {{ $isCompleted || $isCurrent || $isRejected ? '#fff' : '#94a3b8' }};
                                 box-shadow: {{ $isCurrent ? '0 0 0 4px rgba(59,130,246,0.2)' : 'none' }};
                                 transition: all 0.3s ease;">
                                <i class="fas {{ $step['icon'] }}"></i>
                            </div>
                            <div class="small fw-bold {{ $isCurrent ? 'text-primary' : ($isCompleted || $isRejected ? 'text-' . $step['color'] : 'text-muted') }}" style="font-size: 0.65rem;">
                                {{ $step['label'] }}
                            </div>
                        </div>
                        @if(!$loop->last)
                        <div class="flex-fill align-self-start" style="height: 3px; background: {{ $stepIndex < $currentIndex ? 'linear-gradient(90deg, var(--bs-' . $workflowSteps[$wfKeys[$stepIndex]]['color'] . '), var(--bs-' . $workflowSteps[$wfKeys[$stepIndex + 1]]['color'] . '))' : '#e2e8f0' }}; margin-top: 16px; border-radius: 2px;"></div>
                        @endif
                    @endforeach
                </div>
                <div class="text-center mt-2">
                    <span class="badge bg-primary">Statut actuel : {{ $workflowSteps[$currentWf]['label'] ?? ucfirst($currentWf) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de la demande -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">📋 Informations de la Demande</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Demandeur:</strong> {{ $demande->full_name }}</p>
                        <p><strong>Email:</strong> {{ $demande->email }}</p>
                        <p><strong>Téléphone:</strong> {{ $demande->phone }}</p>
                        <p><strong>Région:</strong> {{ $demande->region }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Type:</strong> {{ ucfirst($demande->type) }}</p>
                        <p><strong>Date de demande:</strong> {{ optional($demande->request_date)->format('d/m/Y') ?? \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</p>
                        <p><strong>Statut actuel:</strong> 
                            <span class="badge bg-{{ $demande->status === 'approved' ? 'success' : ($demande->status === 'rejected' ? 'danger' : ($demande->status === 'completed' ? 'info' : 'warning')) }}">
                                {{ ucfirst($demande->status) }}
                            </span>
                        </p>
                        @if($demande->address)
                        <p><strong>Adresse:</strong> {{ $demande->address }}</p>
                        @endif
                    </div>
                </div>
                @if($demande->description)
                <div class="mt-3">
                    <p><strong>Description:</strong></p>
                    <div class="bg-light p-3 rounded">
                        {{ $demande->description }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <!-- Colonne gauche : Workflow & Traitement -->
        <div class="col-lg-6">
            <!-- Formulaire de traitement classique -->
            <div class="card-modern p-3 mb-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-cogs me-2 text-primary"></i>Traitement de la Demande</h6>
                <form action="{{ route('admin.demandes.update', $demande->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label small fw-bold">Statut *</label>
                            <select class="form-select form-select-sm" id="status" name="status" required>
                                <option value="pending" {{ $demande->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="approved" {{ $demande->status === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                <option value="rejected" {{ $demande->status === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                                <option value="completed" {{ $demande->status === 'completed' ? 'selected' : '' }}>Terminée</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label small fw-bold">Assigner à</label>
                            <select class="form-select form-select-sm" id="assigned_to" name="assigned_to">
                                <option value="">Non assigné</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $demande->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->role }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_comment" class="form-label small fw-bold">Commentaire administrateur</label>
                        <textarea class="form-control form-control-sm" id="admin_comment" name="admin_comment" rows="3" 
                                  placeholder="Ajoutez un commentaire...">{{ $demande->admin_comment ?? '' }}</textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            <!-- Avancement du Workflow -->
            <div class="card-modern p-3 mb-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-stream me-2 text-success"></i>Avancer le Workflow</h6>
                <form action="{{ route('admin.demandes.workflow', $demande->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nouveau statut workflow</label>
                        <select class="form-select form-select-sm" name="workflow_status" required>
                            @foreach([
                                'soumise' => 'Soumise',
                                'en_revue' => 'En revue',
                                'document_attente' => 'Attente document',
                                'signee' => 'Signée',
                                'scannee' => 'Scannée',
                                'validee_dg' => 'Validée DG',
                                'approuvee' => 'Approuvée',
                                'rejetee' => 'Rejetée',
                                'cloturee' => 'Clôturée',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ ($demande->workflow_status ?? 'soumise') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Commentaire</label>
                        <textarea class="form-control form-control-sm" name="comment" rows="2" placeholder="Commentaire sur cette transition..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-forward me-1"></i>Avancer le workflow
                    </button>
                </form>
            </div>

            <!-- Upload Document Signé -->
            <div class="card-modern p-3 mb-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-signature me-2 text-primary"></i>Document Signé</h6>
                <form action="{{ route('admin.demandes.signature', $demande->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fichier signé (PDF/Image)</label>
                        <input type="file" name="signature_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Référence courrier</label>
                            <input type="text" name="courier_reference" class="form-control form-control-sm" placeholder="Ref courrier">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Date courrier</label>
                            <input type="date" name="courier_date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Commentaire</label>
                        <textarea class="form-control form-control-sm" name="comment" rows="2" placeholder="Notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload me-1"></i>Enregistrer le document signé
                    </button>
                </form>
            </div>
        </div>

        <!-- Colonne droite : Scan + Validation DG -->
        <div class="col-lg-6">
            <!-- Upload Scan -->
            <div class="card-modern p-3 mb-2">
                <h6 class="fw-bold mb-3"><i class="fas fa-file-pdf me-2 text-info"></i>Scan du Document</h6>
                <form action="{{ route('admin.demandes.scan', $demande->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Fichier scan (PDF/Image)</label>
                        <input type="file" name="scan_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Commentaire</label>
                        <textarea class="form-control form-control-sm" name="comment" rows="2" placeholder="Notes sur le scan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-info btn-sm text-white">
                        <i class="fas fa-upload me-1"></i>Enregistrer le scan
                    </button>
                </form>
            </div>

            <!-- Validation DG -->
            <div class="card-modern p-3 mb-2 border-primary">
                <h6 class="fw-bold mb-3"><i class="fas fa-stamp me-2 text-warning"></i>Validation par le DG</h6>
                <form action="{{ route('admin.demandes.dg-validate', $demande->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Décision DG</label>
                        <div class="d-flex gap-2">
                            <button type="submit" name="dg_approved" value="1" class="btn btn-success btn-sm flex-fill">
                                <i class="fas fa-check me-1"></i>Valider
                            </button>
                            <button type="submit" name="dg_approved" value="0" class="btn btn-danger btn-sm flex-fill">
                                <i class="fas fa-times me-1"></i>Rejeter
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Commentaire DG</label>
                        <textarea class="form-control form-control-sm" name="dg_comment" rows="3" placeholder="Commentaire de la Direction Générale..."></textarea>
                    </div>
                </form>
            </div>

            <!-- Documents existants -->
            @if($demande->dg_signature_file || $demande->scan_file)
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-folder-open me-2 text-warning"></i>Documents enregistrés</h6>
                @if($demande->dg_signature_file)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                    <div class="small">
                        <i class="fas fa-signature text-primary me-1"></i>Document signé
                    </div>
                    <a href="{{ asset('storage/' . $demande->dg_signature_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @endif
                @if($demande->scan_file)
                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                    <div class="small">
                        <i class="fas fa-file-pdf text-info me-1"></i>Scan
                    </div>
                    <a href="{{ asset('storage/' . $demande->scan_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection