<div class="tab-pane fade show active" id="messages" role="tabpanel">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Messages Récents</h6>
            <a href="{{ route('ctc.messages.index') }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Voir tout</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>ID</th><th>Expéditeur</th><th>Email</th><th>Objet</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentMessages as $message)
                        <tr class="{{ !$message->lu ? 'table-info' : '' }}">
                            <td>{{ $message->id }}</td>
                            <td>{{ $message->expediteur ?? 'N/A' }}</td>
                            <td>{{ $message->email_expediteur ?? '—' }}</td>
                            <td>{{ $message->sujet ?? 'Sans objet' }}</td>
                            <td>@if($message->lu)<span class="badge bg-success">Lu</span>@else<span class="badge bg-warning">Non lu</span>@endif</td>
                            <td>{{ $message->created_at ? \Carbon\Carbon::parse($message->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td><a href="{{ route('ctc.messages.show', $message->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center">Aucun message récent</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="notifications" role="tabpanel">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Notifications Récentes</h6>
            <a href="{{ route('ctc.notifications.index') }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Voir tout</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>ID</th><th>Type</th><th>Message</th><th>Statut</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentNotifications as $notification)
                        <tr class="{{ !$notification->read ? 'table-info' : '' }}">
                            <td>{{ $notification->id }}</td>
                            <td><span class="badge bg-primary">{{ $notification->type ?? 'Info' }}</span></td>
                            <td>{{ $notification->message ?? 'N/A' }}</td>
                            <td>@if($notification->read)<span class="badge bg-success">Lue</span>@else<span class="badge bg-warning">Non lue</span>@endif</td>
                            <td>{{ $notification->created_at ? \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Aucune notification récente</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="newsletter" role="tabpanel">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Campagnes Newsletter</h6>
            <a href="{{ route('ctc.newsletter.index') }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Voir tout</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>ID</th><th>Titre</th><th>Sujet</th><th>Statut</th><th>Destinataires</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentNewsletters as $newsletter)
                        <tr>
                            <td>{{ $newsletter->id }}</td>
                            <td>{{ $newsletter->title ?? 'N/A' }}</td>
                            <td>{{ $newsletter->subject ?? 'N/A' }}</td>
                            <td>@php $sc = match($newsletter->status ?? '') { 'sent'=>'success','sending'=>'info','pending'=>'warning','draft'=>'secondary','failed'=>'danger', default=>'secondary' }; @endphp
                                <span class="badge bg-{{ $sc }}">{{ ucfirst($newsletter->status ?? 'N/A') }}</span></td>
                            <td>{{ $newsletter->recipients_count ?? 0 }}</td>
                            <td>{{ $newsletter->created_at ? \Carbon\Carbon::parse($newsletter->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">Aucune campagne récente</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="subscribers" role="tabpanel">
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Abonnés Newsletter</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>ID</th><th>Email</th><th>Nom</th><th>Statut</th><th>Date inscription</th><th>Source</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentSubscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->id }}</td>
                            <td>{{ $subscriber->email ?? 'N/A' }}</td>
                            <td>{{ $subscriber->name ?? 'N/A' }}</td>
                            <td>@if(in_array($subscriber->status ?? '', ['subscribed', 'active']))<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">{{ ucfirst($subscriber->status ?? 'N/A') }}</span>@endif</td>
                            <td>{{ isset($subscriber->subscribed_at) ? \Carbon\Carbon::parse($subscriber->subscribed_at)->format('d/m/Y H:i') : ($subscriber->created_at ? \Carbon\Carbon::parse($subscriber->created_at)->format('d/m/Y H:i') : 'N/A') }}</td>
                            <td>{{ $subscriber->source ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">Aucun abonné récent</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="audit" role="tabpanel">
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Logs d'Audit des Communications</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>ID</th><th>Action</th><th>Utilisateur</th><th>Détails</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td><span class="badge bg-info">{{ $log->action ?? 'N/A' }}</span></td>
                            <td>{{ $log->user_name ?? 'Système' }}</td>
                            <td>{{ $log->details ?? 'N/A' }}</td>
                            <td>{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Aucun log d'audit récent</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
