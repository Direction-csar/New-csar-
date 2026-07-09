<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    protected $fillable = [
        'prenoms_nom',
        'date_naissance',
        'lieu_naissance',
        'tranche_age',
        'nationalite',
        'numero_cni',
        'sexe',
        'situation_matrimoniale',
        'nombre_enfants',
        'contact_telephonique',
        'email',
        'groupe_sanguin',
        'adresse_complete',
        'matricule',
        'date_recrutement_csar',
        'date_prise_service_csar',
        'statut',
        'poste_actuel',
        'salaire_base',
        'sursalaire',
        'indemnite_transport',
        'indemnite_fonction',
        'salaire_brut',
        'net_a_payer',
        'categorie',
        'filiation',
        'date_delivrance_id',
        'nombre_epouses',
        'type_contrat',
        'direction_service',
        'localisation_region',
        'dernier_poste_avant_csar',
        'formations_professionnelles',
        'diplome_academique',
        'autres_diplomes_certifications',
        'logiciels_maitrises',
        'langues_parlees',
        'autres_aptitudes',
        'aspirations_professionnelles',
        'interet_nouvelles_responsabilites',
        'photo_personnelle',
        'taille_vetements',
        'contact_urgence_nom',
        'contact_urgence_telephone',
        'contact_urgence_lien_parente',
        'observations_personnelles',
        'statut_validation',
        'commentaire_validation',
        'valide_par',
        'date_validation'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_recrutement_csar' => 'date',
        'date_prise_service_csar' => 'date',
        'date_validation' => 'datetime',
        'logiciels_maitrises' => 'array',
        'langues_parlees' => 'array',
        'nombre_enfants' => 'integer'
    ];

    // Relations
    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // Accessors
    public function getPhotoPathAttribute()
    {
        if ($this->photo_personnelle) {
            return storage_path('app/public/personnel/' . $this->photo_personnelle);
        }
        return null;
    }

    // Scopes
    public function scopeValides($query)
    {
        return $query->where('statut_validation', 'Validé');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut_validation', 'En attente');
    }

    public function scopeParDirection($query, $direction)
    {
        return $query->where('direction_service', $direction);
    }

    public function scopeParPoste($query, $poste)
    {
        return $query->where('poste_actuel', $poste);
    }

    public function scopeParRegion($query, $region)
    {
        return $query->where('localisation_region', $region);
    }

    // Accesseurs
    public function getAgeAttribute()
    {
        return $this->date_naissance->age;
    }

    public function getAncienneteAttribute()
    {
        return $this->date_recrutement_csar->diffInYears(now());
    }

    public function getPhotoUrlAttribute(): string
    {
        $fileName = $this->photo_personnelle;
        if (!empty($fileName)) {
            // Vérifier plusieurs emplacements possibles
            $possiblePaths = [
                'personnel/' . $fileName,
                $fileName,
                'public/personnel/' . $fileName
            ];

            foreach ($possiblePaths as $path) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }

            // Vérifier aussi directement dans public/storage
            $publicPath = public_path('storage/personnel/' . $fileName);
            if (file_exists($publicPath)) {
                return asset('storage/personnel/' . $fileName);
            }

            // Log pour debug
            \Log::warning("Photo du personnel non trouvée: {$fileName}");
        }

        // Retourner une image par défaut stylisée
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="#f0f0f0"/><circle cx="100" cy="70" r="35" fill="#ccc"/><path d="M 40 160 Q 40 120 100 120 Q 160 120 160 160" fill="#ccc"/><text x="100" y="190" font-size="12" text-anchor="middle" fill="#999">Photo non disponible</text></svg>');
    }

    // Méthodes
    public function valider($userId, $commentaire = null)
    {
        $this->update([
            'statut_validation' => 'Validé',
            'valide_par' => $userId,
            'date_validation' => now(),
            'commentaire_validation' => $commentaire
        ]);
    }

    public function rejeter($userId, $commentaire = null)
    {
        $this->update([
            'statut_validation' => 'Rejeté',
            'valide_par' => $userId,
            'date_validation' => now(),
            'commentaire_validation' => $commentaire
        ]);
    }

    public static function genererMatricule()
    {
        do {
            $matricule = 'CSAR-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('matricule', $matricule)->exists());

        return $matricule;
    }
}
