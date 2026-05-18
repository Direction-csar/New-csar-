@extends('layouts.drh-portal')

@section('title', 'Modifier le Personnel')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Réutiliser les styles de la page index */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
        --shadow-strong: 0 20px 40px rgba(0, 0, 0, 0.2);
        --border-radius: 20px;
        --border-radius-sm: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-effect {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        box-shadow: var(--shadow-soft);
    }

    .edit-header-card {
        background: var(--primary-gradient);
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-medium);
        transition: var(--transition);
        overflow: hidden;
        position: relative;
    }

    .edit-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.1;
        pointer-events: none;
    }

    .form-card-3d {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        padding: 2.5rem;
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--glass-border);
        position: relative;
        overflow: hidden;
    }

    .form-card-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .form-control-3d {
        border: 2px solid #e2e8f0;
        border-radius: var(--border-radius-sm);
        padding: 14px 18px;
        transition: var(--transition);
        background: rgba(248, 250, 252, 0.8);
        backdrop-filter: blur(10px);
        font-weight: 500;
    }

    .form-control-3d:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        background: white;
        transform: translateY(-2px);
        color: #1a202c;
    }

    .form-control-3d {
        color: #2d3748;
    }

    .form-control-3d::placeholder {
        color: #718096;
        opacity: 1;
    }

    .btn-3d {
        border-radius: var(--border-radius-sm);
        padding: 14px 28px;
        font-weight: 700;
        border: none;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
        transform-style: preserve-3d;
    }

    .btn-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s;
    }

    .btn-3d:hover::before {
        left: 100%;
    }

    .btn-3d:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .btn-primary-3d {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .btn-success-3d {
        background: var(--success-gradient);
        color: white;
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
    }

    .btn-warning-3d {
        background: var(--warning-gradient);
        color: white;
        box-shadow: 0 8px 25px rgba(250, 112, 154, 0.3);
    }

    .btn-danger-3d {
        background: var(--danger-gradient);
        color: white;
        box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
    }

    .loading-spinner-3d {
        display: inline-block;
        width: 24px;
        height: 24px;
        border: 3px solid rgba(102, 126, 234, 0.2);
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin-3d 1s linear infinite;
    }

    @keyframes spin-3d {
        0% { transform: rotate(0deg) scale(1); }
        50% { transform: rotate(180deg) scale(1.1); }
        100% { transform: rotate(360deg) scale(1); }
    }

    .fade-in-3d {
        animation: fadeIn3D 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fadeIn3D {
        from { 
            opacity: 0; 
            transform: translateY(30px) rotateX(-10deg); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0) rotateX(0deg); 
        }
    }

    .form-label {
        font-weight: 600;
        color: #1a202c !important;
        margin-bottom: 0.5rem;
    }

    .edit-header-card h1,
    .edit-header-card p {
        color: white !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .required {
        color: #e53e3e;
    }

    .back-btn {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-medium);
        transition: var(--transition);
    }

    .back-btn:hover {
        transform: translateY(-3px) scale(1.1);
        box-shadow: var(--shadow-strong);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Bouton retour -->
    <a href="{{ route('admin.drh.personnel.index') }}" class="back-btn" title="Retour à la liste">
        <i class="fas fa-arrow-left"></i>
    </a>

    <!-- En-tête moderne avec effet 3D -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="edit-header-card p-4 fade-in-3d">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-white">
                        <h1 class="h2 mb-2 fw-bold">
                            <i class="fas fa-user-edit me-3"></i>Modifier le Personnel
                        </h1>
                        <p class="mb-0 opacity-90">Modifiez les informations de {{ $personnel->prenoms_nom }}</p>
                    </div>
                    <div class="d-flex gap-3">
                        <button class="btn btn-3d btn-warning-3d" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Réinitialiser
                        </button>
                        <button class="btn btn-3d btn-danger-3d" onclick="deleteUser()">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire moderne avec effet de verre -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card-3d fade-in-3d">
                <form id="editPersonnelForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom complet <span class="required">*</span></label>
                            <input type="text" class="form-control form-control-3d" id="name" name="prenoms_nom" 
                                   value="{{ $personnel->prenoms_nom }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" class="form-control form-control-3d" id="email" name="email" 
                                   value="{{ $personnel->email }}" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control form-control-3d" id="phone" name="contact_telephonique" 
                                   value="{{ $personnel->contact_telephonique }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Poste actuel <span class="required">*</span></label>
                            <input type="text" class="form-control form-control-3d" id="role" name="poste_actuel" 
                                   value="{{ $personnel->poste_actuel }}" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Direction/Service</label>
                            <input type="text" class="form-control form-control-3d" id="department" name="direction_service" 
                                   value="{{ $personnel->direction_service }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <input type="text" class="form-control form-control-3d" id="status" name="statut" 
                                   value="{{ $personnel->statut }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea class="form-control form-control-3d" id="address" name="adresse_complete" rows="3">{{ $personnel->adresse_complete }}</textarea>
                    </div>

                    <!-- Informations système -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date de création</label>
                            <input type="text" class="form-control form-control-3d" 
                                   value="{{ $personnel->created_at->format('d/m/Y à H:i') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dernière modification</label>
                            <input type="text" class="form-control form-control-3d" 
                                   value="{{ $personnel->updated_at->format('d/m/Y à H:i') }}" readonly>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <button type="button" class="btn btn-3d btn-warning-3d me-3" onclick="resetPassword()">
                                <i class="fas fa-key me-2"></i>Réinitialiser le mot de passe
                            </button>
                        </div>
                        <div>
                            <a href="{{ route('admin.drh.personnel.index') }}" class="btn btn-3d btn-warning-3d me-3">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-3d btn-primary-3d">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du formulaire
    document.getElementById('editPersonnelForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updatePersonnel();
    });
});

function updatePersonnel() {
    const formData = new FormData(document.getElementById('editPersonnelForm'));
    const submitBtn = document.querySelector('#editPersonnelForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Afficher le loading
    submitBtn.innerHTML = '<span class="loading-spinner-3d me-2"></span>Enregistrement...';
    submitBtn.disabled = true;
    
    fetch('{{ route("admin.personnel.update", $personnel->id) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.personnel.index") }}';
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la mise à jour du personnel', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function resetForm() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
        document.getElementById('editPersonnelForm').reset();
        // Remettre les valeurs originales
        document.getElementById('name').value = '{{ $personnel->prenoms_nom }}';
        document.getElementById('email').value = '{{ $personnel->email }}';
        document.getElementById('phone').value = '{{ $personnel->contact_telephonique }}';
        document.getElementById('role').value = '{{ $personnel->poste_actuel }}';
        document.getElementById('department').value = '{{ $personnel->direction_service }}';
        document.getElementById('status').value = '{{ $personnel->statut }}';
        document.getElementById('address').value = '{{ $personnel->adresse_complete }}';
    }
}

function deleteUser() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce personnel ? Cette action est irréversible.')) {
        fetch('{{ route("admin.personnel.destroy", $personnel->id) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("admin.personnel.index") }}';
                }, 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la suppression', 'error');
        });
    }
}

function resetPassword() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de ce personnel ?')) {
        fetch('{{ route("admin.personnel.reset-password", $personnel->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la réinitialisation du mot de passe', 'error');
        });
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' :
                   type === 'error' ? 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)' :
                   type === 'warning' ? 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' :
                   'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        background: ${bgColor};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        font-weight: 500;
        animation: slideIn 0.3s ease;
    `;
    
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close btn-close-white ms-3" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Ajouter les animations CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>
@endpush
