<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $table = 'demandes';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'objet',
        'description',
        'pj',
        'consentement',
        'type_demande',
        'tracking_code',
        'code_suivi',
        'statut',
        'reponse',
        'date_traitement',
        'traite_par',
        'commentaire_admin',
        'assignee_id',
        'priorite',
        'region',
        'commune',
        'departement',
        'adresse',
        'date_demande',
        'nom_demandeur',
        'latitude',
        'longitude',
        'sms_envoye',
        'sms_message_id',
        'sms_envoye_at'
    ];

    protected $casts = [
        'date_traitement' => 'datetime',
        'consentement' => 'boolean'
    ];

    /**
     * Scope pour récupérer les demandes par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour récupérer les demandes par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_demande', $type);
    }

    /**
     * Scope pour récupérer les demandes par région
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope pour récupérer les demandes récentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Accessor pour obtenir le statut en français
     */
    public function getStatutFrancaisAttribute()
    {
        $statuts = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'approuvee' => 'Approuvée',
            'rejetee' => 'Rejetée',
            'terminee' => 'Terminée'
        ];

        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Accessor pour obtenir le type en français
     */
    public function getTypeFrancaisAttribute()
    {
        $types = [
            'aide_alimentaire' => 'Aide alimentaire',
            'aide_medicale' => 'Aide médicale',
            'aide_financiere' => 'Aide financière',
            'information_generale' => 'Information générale',
            'demande_audience' => 'Demande d\'audience',
            'autre' => 'Autre'
        ];

        return $types[$this->type_demande] ?? $this->type_demande;
    }

    /**
     * Accessor pour obtenir la priorité en français
     */
    public function getPrioriteFrancaisAttribute()
    {
        $priorites = [
            'faible' => 'Faible',
            'moyenne' => 'Moyenne',
            'haute' => 'Haute',
            'urgente' => 'Urgente'
        ];

        return $priorites[$this->priorite] ?? $this->priorite;
    }

    /**
     * Méthode pour approuver une demande
     */
    public function approuver($commentaire = null)
    {
        $this->update([
            'statut' => 'approuvee',
            'date_traitement' => now()->toDateString(),
            'commentaire_admin' => $commentaire
        ]);
    }

    /**
     * Méthode pour rejeter une demande
     */
    public function rejeter($commentaire = null)
    {
        $this->update([
            'statut' => 'rejetee',
            'date_traitement' => now()->toDateString(),
            'commentaire_admin' => $commentaire
        ]);
    }

    /**
     * Méthode pour marquer comme terminée
     */
    public function terminer($commentaire = null)
    {
        $this->update([
            'statut' => 'terminee',
            'date_traitement' => now()->toDateString(),
            'commentaire_admin' => $commentaire
        ]);
    }

    /**
     * Méthode pour assigner à un utilisateur
     */
    public function assigner($userId)
    {
        $this->update(['assignee_id' => $userId]);
    }

    /**
     * Relation avec l'utilisateur assigné
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Générer un code de suivi unique
     */
    public static function generateCodeSuivi()
    {
        do {
            $code = 'CSAR-' . strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('code_suivi', $code)->exists());

        return $code;
    }
}
