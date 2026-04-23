<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'status',
        'confirmation_token',
        'confirmed_at',
        'subscribed_at',
        'unsubscribed_at',
        'source',
        'preferences',
        'last_email_sent_at',
        'email_count'
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'preferences' => 'array',
        'email_count' => 'integer'
    ];

    /**
     * Attribut virtuel pour la compatibilité avec l'ancien champ is_active
     */
    protected $appends = ['is_active'];

    /**
     * Accesseur pour is_active (compatibilité)
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function confirm(): void
    {
        $this->update([
            'status' => 'active',
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
    }

    /**
     * Mutateur pour is_active (compatibilité)
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['status'] = $value ? 'active' : 'inactive';
    }

    /**
     * Scope pour les abonnés actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les abonnés inactifs
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope pour les abonnés désabonnés
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    /**
     * S'abonner à la newsletter
     */
    public function subscribe($email, $name = null, $source = 'website')
    {
        return static::create([
            'email' => $email,
            'name' => $name,
            'status' => 'active',
            'subscribed_at' => now(),
            'source' => $source,
            'preferences' => [
                'frequency' => 'weekly',
                'categories' => ['news', 'reports', 'events']
            ]
        ]);
    }

    /**
     * Se désabonner de la newsletter
     */
    public function unsubscribe()
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now()
        ]);
    }

    /**
     * Marquer comme inactif
     */
    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    /**
     * Réactiver l'abonnement
     */
    public function reactivate()
    {
        $this->update([
            'status' => 'active',
            'subscribed_at' => now()
        ]);
    }

    /**
     * Mettre à jour les préférences
     */
    public function updatePreferences($preferences)
    {
        $this->update(['preferences' => $preferences]);
    }

    /**
     * Incrémenter le compteur d'emails envoyés
     */
    public function incrementEmailCount()
    {
        $this->increment('email_count');
        $this->update(['last_email_sent_at' => now()]);
    }

    /**
     * Obtenir les statistiques des abonnés
     */
    public static function getStats()
    {
        return [
            'total' => static::count(),
            'active' => static::active()->count(),
            'inactive' => static::inactive()->count(),
            'unsubscribed' => static::unsubscribed()->count(),
            'new_this_month' => static::where('subscribed_at', '>=', now()->startOfMonth())->count(),
            'unsubscribed_this_month' => static::where('unsubscribed_at', '>=', now()->startOfMonth())->count()
        ];
    }
}