<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'level',
        'status',
        'model_type',
        'model_id',
        'user_id',
        'ip_address',
        'user_agent',
        'description',
        'data',
        'created_at'
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour filtrer par type de modèle
     */
    public function scopeModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope pour filtrer par utilisateur
     */
    public function scopeUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour les actions récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope pour filtrer par niveau
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les alertes critiques
     */
    public function scopeCritical($query)
    {
        return $query->where('level', 'critical');
    }

    /**
     * Scope pour les avertissements
     */
    public function scopeWarning($query)
    {
        return $query->where('level', 'warning');
    }

    /**
     * Obtenir les statistiques d'audit
     */
    public static function getAuditStats($days = 30)
    {
        $query = static::where('created_at', '>=', now()->subDays($days));
        
        return [
            'total_actions' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'actions_by_type' => $query->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'actions_by_model' => $query->selectRaw('model_type, COUNT(*) as count')
                ->groupBy('model_type')
                ->orderBy('count', 'desc')
                ->get(),
            'daily_activity' => $query->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get()
        ];
    }
}