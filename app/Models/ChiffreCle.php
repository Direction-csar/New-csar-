<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ChiffreCle extends Model
{
    use HasFactory;

    protected $table = 'chiffres_cles';
    
    /**
     * Vérifier si la table existe avant d'utiliser le modèle
     */
    public static function tableExists()
    {
        try {
            return Schema::hasTable('chiffres_cles');
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Méthode statique sécurisée pour récupérer les chiffres clés actifs
     * Retourne une collection vide si la table n'existe pas
     */
    public static function safeGetActifs()
    {
        try {
            // Vérifier d'abord si la table existe
            if (!self::tableExists()) {
                return collect();
            }
            
            // Utiliser Eloquent directement pour récupérer les données
            // Cela garantit que les objets ont tous leurs attributs (notamment id)
            return self::where('statut', 'Actif')
                ->orderBy('ordre')
                ->get();
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Si c'est une erreur SQL (table n'existe pas), retourner une collection vide
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Base table")) {
                return collect();
            }
            // Pour les autres erreurs SQL, retourner aussi une collection vide
            return collect();
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une collection vide
            return collect();
        }
    }

    protected $fillable = [
        'icone',
        'titre',
        'valeur',
        'description',
        'couleur',
        'statut',
        'ordre'
    ];

    protected $casts = [
        'ordre' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope pour récupérer seulement les chiffres clés actifs
     */
    public function scopeActifs($query)
    {
        try {
            if (!self::tableExists()) {
                return $query->whereRaw('1 = 0'); // Retourne une requête vide
            }
            return $query->where('statut', 'Actif');
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une requête vide
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        try {
            if (!self::tableExists()) {
                return $query; // Retourne la requête telle quelle
            }
            return $query->orderBy('ordre');
        } catch (\Exception $e) {
            // En cas d'erreur, retourner la requête telle quelle
            return $query;
        }
    }
    
    /**
     * Accessor pour obtenir le statut en français
     */
    public function getStatutFrancaisAttribute()
    {
        return $this->statut === 'Actif' ? 'Actif' : 'Inactif';
    }

    /**
     * Accessor pour obtenir la couleur avec # si nécessaire
     */
    public function getCouleurCompleteAttribute()
    {
        return str_starts_with($this->couleur, '#') ? $this->couleur : '#' . $this->couleur;
    }

    /**
     * Méthode pour activer un chiffre clé
     */
    public function activer()
    {
        $this->update(['statut' => 'Actif']);
    }

    /**
     * Méthode pour désactiver un chiffre clé
     */
    public function desactiver()
    {
        $this->update(['statut' => 'Inactif']);
    }

    /**
     * Méthode pour basculer le statut
     */
    public function basculerStatut()
    {
        $this->update(['statut' => $this->statut === 'Actif' ? 'Inactif' : 'Actif']);
    }
}
