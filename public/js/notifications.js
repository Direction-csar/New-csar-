/**
 * Système de notifications CSAR
 * Gestion complète des notifications en temps réel
 */

class NotificationSystem {
    constructor() {
        this.refreshInterval = 30000; // 30 secondes
        this.intervalId = null;
        this.container = document.getElementById('notifications-container');
        this.badge = document.getElementById('notificationBadge');
        this.bellBtn = document.getElementById('notificationBellBtn');
        
        this.init();
    }

    init() {
        // Charger les notifications au démarrage
        this.loadNotifications();
        
        // Démarrer le rafraîchissement automatique
        this.startAutoRefresh();
        
        // Écouter les événements
        this.attachEventListeners();
    }

    attachEventListeners() {
        // Rafraîchir lors de l'ouverture du dropdown
        if (this.bellBtn) {
            this.bellBtn.addEventListener('click', () => {
                this.loadNotifications();
            });
        }
    }

    startAutoRefresh() {
        // Rafraîchir automatiquement toutes les 30 secondes
        this.intervalId = setInterval(() => {
            this.updateBadge();
        }, this.refreshInterval);
    }

    stopAutoRefresh() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    }

    async loadNotifications() {
        try {
            this.showLoading();
            
            const response = await fetch('/admin/api/notifications?limit=10', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des notifications');
            }

            const data = await response.json();
            
            if (data.success) {
                this.renderNotifications(data.notifications);
                this.updateBadge(data.unread_count);
            } else {
                this.showError('Impossible de charger les notifications');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de connexion');
        }
    }

    renderNotifications(notifications) {
        if (!this.container) return;

        if (notifications.length === 0) {
            this.container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Aucune notification</p>
                </div>
            `;
            return;
        }

        this.container.innerHTML = notifications.map(notification => {
            const iconColor = this.getIconColor(notification.type);
            const icon = notification.icon || 'bell';
            
            return `
                <li class="dropdown-item notification-item ${notification.read ? 'read' : 'unread'}" 
                    data-notification-id="${notification.id}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notification-icon-circle bg-${iconColor}-soft">
                            <i class="fas fa-${icon} text-${iconColor}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="notification-title mb-0">
                                    ${notification.title}
                                    ${!notification.read ? '<span class="badge bg-primary badge-sm ms-1">Nouveau</span>' : ''}
                                </h6>
                                <button type="button" 
                                        class="btn btn-sm btn-link text-danger p-0 ms-2" 
                                        onclick="notificationSystem.deleteNotification(${notification.id})"
                                        title="Supprimer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <p class="notification-message text-muted mb-2">${notification.message}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>${notification.time_ago}
                                </small>
                                <div class="notification-actions">
                                    ${notification.action_url ? 
                                        `<a href="${notification.action_url}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>` : ''}
                                    ${!notification.read ? 
                                        `<button type="button" 
                                                class="btn btn-sm btn-outline-success" 
                                                onclick="notificationSystem.markAsRead(${notification.id})">
                                            <i class="fas fa-check"></i>
                                        </button>` : 
                                        `<button type="button" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                onclick="notificationSystem.markAsUnread(${notification.id})">
                                            <i class="fas fa-envelope"></i>
                                        </button>`}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            `;
        }).join('');

        // Ajouter le bouton "Voir tout"
        this.container.innerHTML += `
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-item text-center">
                <a href="/admin/notifications" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-list"></i> Voir toutes les notifications
                </a>
            </li>
        `;
    }

    async updateBadge(count = null) {
        if (!this.badge) return;

        if (count === null) {
            try {
                const response = await fetch('/admin/api/notifications/count', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.status === 401 || response.status === 403) {
                    this.stopAutoRefresh();
                    return;
                }
                if (response.ok) {
                    const data = await response.json();
                    count = data.count || 0;
                }
            } catch (error) {
                return;
            }
        }

        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.style.display = 'inline-block';
        } else {
            this.badge.style.display = 'none';
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/admin/api/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la mise à jour');
            }

            const data = await response.json();
            
            if (data.success) {
                // Mettre à jour l'affichage
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                }
                
                this.updateBadge(data.unread_count);
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur lors de la mise à jour', 'error');
        }
    }

    async markAsUnread(notificationId) {
        try {
            const response = await fetch(`/admin/api/notifications/${notificationId}/mark-unread`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la mise à jour');
            }

            const data = await response.json();
            
            if (data.success) {
                // Mettre à jour l'affichage
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('read');
                    item.classList.add('unread');
                }
                
                this.updateBadge(data.unread_count);
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur lors de la mise à jour', 'error');
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/admin/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la mise à jour');
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(0);
                this.loadNotifications();
                this.showToast('Toutes les notifications ont été marquées comme lues', 'success');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur lors de la mise à jour', 'error');
        }
    }

    async deleteNotification(notificationId) {
        if (!confirm('Voulez-vous vraiment supprimer cette notification ?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/api/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la suppression');
            }

            const data = await response.json();
            
            if (data.success) {
                // Retirer l'élément du DOM
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.remove();
                }
                
                this.updateBadge(data.unread_count);
                this.loadNotifications();
                this.showToast('Notification supprimée', 'success');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showToast('Erreur lors de la suppression', 'error');
        }
    }

    showLoading() {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="text-center py-3" id="notifications-loading">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
    }

    showError(message) {
        if (!this.container) return;
        
        this.container.innerHTML = `
            <div class="text-center py-3 text-danger">
                <i class="fas fa-exclamation-triangle mb-2"></i>
                <p class="mb-0">${message}</p>
            </div>
        `;
    }

    showToast(message, type = 'info') {
        // Utiliser une bibliothèque de toasts si disponible, sinon alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }

    getIconColor(type) {
        const colors = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info',
            'demande': 'primary',
            'message': 'info',
            'newsletter': 'success',
            'communication': 'purple'
        };
        
        return colors[type] || 'info';
    }
}

// Fonction globale pour marquer toutes les notifications comme lues
function markAllNotificationsAsRead() {
    if (window.notificationSystem) {
        window.notificationSystem.markAllAsRead();
    }
}

// Initialiser le système au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    window.notificationSystem = new NotificationSystem();
});

// S'assurer que le système reste actif
window.addEventListener('focus', function() {
    if (window.notificationSystem) {
        window.notificationSystem.updateBadge();
    }
});

