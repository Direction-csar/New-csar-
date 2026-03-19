@extends('layouts.ctc')

@section('title', 'Centre de Communication & Publications')
@section('page-title', 'Centre de Communication & Publications')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">📱 Centre de Communication & Publications</h1>
            <p class="text-muted">Gérez les publications de la plateforme : actualités, rapports, newsletter, messages</p>
        </div>
        <button class="btn btn-primary" id="refreshStats">
            <i class="fas fa-sync-alt"></i> Actualiser
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-th-large me-2"></i>Accès rapide aux publications</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4 col-lg-2">
                            <a href="{{ route('ctc.actualites.index') }}" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-newspaper fa-2x mb-2"></i>
                                <span>Actualités</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <a href="{{ route('ctc.sim-reports.index') }}" class="btn btn-outline-success w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <span>Rapports</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <a href="{{ route('ctc.newsletter.index') }}" class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-mail-bulk fa-2x mb-2"></i>
                                <span>Newsletter</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <a href="{{ route('ctc.galerie.index') }}" class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-images fa-2x mb-2"></i>
                                <span>Galerie</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <a href="{{ route('ctc.messages.index') }}" class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-3">
                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                <span>Messages</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Communications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-total-communications">{{ $stats['total_communications'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-comments fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aujourd'hui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-today-communications">{{ $stats['today_communications'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Messages Non Lus</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-unread-messages">{{ $stats['unread_messages'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-envelope fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Notifications Non Lues</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-unread-notifications">{{ $stats['unread_notifications'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-bell fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="communicationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">
                <i class="fas fa-envelope"></i> Messages ({{ $stats['total_messages'] }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                <i class="fas fa-bell"></i> Notifications ({{ $stats['total_notifications'] }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="newsletter-tab" data-bs-toggle="tab" data-bs-target="#newsletter" type="button" role="tab">
                <i class="fas fa-newspaper"></i> Newsletter ({{ $stats['total_newsletters'] }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button" role="tab">
                <i class="fas fa-users"></i> Abonnés ({{ $stats['total_subscribers'] }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab">
                <i class="fas fa-history"></i> Audit
            </button>
        </li>
    </ul>

    <div class="tab-content" id="communicationTabsContent">
        @include('ctc.communications._tabs')
    </div>
</div>

<style>
.stat-box { background: #f8f9fc; border-radius: 8px; padding: 15px; text-align: center; border-left: 4px solid #0d6efd; }
.stat-label { font-size: 0.85rem; color: #858796; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #5a5c69; }
.table-info { background-color: rgba(13, 110, 253, 0.05); }
.nav-tabs .nav-link { color: #0d6efd; font-weight: 600; }
.nav-tabs .nav-link.active { color: #0d6efd; border-color: #dee2e6 #dee2e6 #fff; }
</style>

<script>
document.getElementById('refreshStats').addEventListener('click', function() {
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualisation...';
    this.disabled = true;
    fetch('{{ route("ctc.communications.realtime-stats") }}')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-total-communications').textContent = data.stats.total_communications;
                document.getElementById('stat-today-communications').textContent = data.stats.today_communications;
                document.getElementById('stat-unread-messages').textContent = data.stats.unread_messages;
                document.getElementById('stat-unread-notifications').textContent = data.stats.unread_notifications;
                setTimeout(() => location.reload(), 500);
            }
        })
        .catch(e => { alert('Erreur lors de l\'actualisation'); })
        .finally(() => { this.innerHTML = '<i class="fas fa-sync-alt"></i> Actualiser'; this.disabled = false; });
});
</script>
@endsection
