<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_name',
        'acronym',
        'founded_year',
        'headquarters',
        'phone',
        'email',
        'website',
        'mission',
        'vision',
        'values',
        'leadership',
        'partners',
        'certifications',
        'stats',
        'is_active'
    ];

    protected $casts = [
        'leadership' => 'array',
        'partners' => 'array',
        'certifications' => 'array',
        'stats' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Obtenir les statistiques actuelles
     */
    public function getCurrentStats()
    {
        return [
            'founded_year' => $this->founded_year ?? 2010,
            'total_staff' => Personnel::count(),
            'total_warehouses' => Warehouse::count(),
            'beneficiaries' => PublicRequest::count()
        ];
    }

    /**
     * Obtenir les informations générales
     */
    public function getGeneralInfo()
    {
        return [
            'official_name' => $this->official_name ?? 'Comité de Secours et d\'Assistance aux Réfugiés (CSAR)',
            'acronym' => $this->acronym ?? 'CSAR',
            'founded_year' => $this->founded_year ?? 2010,
            'headquarters' => $this->headquarters ?? 'Dakar, Sénégal',
            'phone' => $this->phone ?? '+221 77 645 92 42',
            'email' => $this->email ?? 'contact@csar.sn',
            'website' => $this->website ?? 'https://www.csar.sn'
        ];
    }

    /**
     * Obtenir la mission et la vision
     */
    public function getMissionVision()
    {
        return [
            'mission' => $this->mission ?? 'Le CSAR s\'engage à fournir une assistance humanitaire d\'urgence et des services de secours aux réfugiés et aux populations vulnérables au Sénégal et dans la région. Notre mission est de sauver des vies, d\'atténuer les souffrances et de protéger la dignité humaine dans les situations de crise.',
            'vision' => $this->vision ?? 'Nous aspirons à un monde où chaque personne déplacée ou réfugiée a accès à une assistance humanitaire de qualité, à la protection et à des solutions durables. Le CSAR vise à devenir un acteur de référence dans la réponse humanitaire en Afrique de l\'Ouest.',
            'values' => $this->values ?? 'Humanité, Neutralité, Impartialité, Indépendance, Volontariat, Unité, Universalité'
        ];
    }

    /**
     * Obtenir l'équipe dirigeante
     */
    public function getLeadership()
    {
        return $this->leadership ?? [];
    }

    /**
     * Obtenir les partenaires
     */
    public function getPartners()
    {
        return $this->partners ?? [];
    }

    /**
     * Obtenir les certifications
     */
    public function getCertifications()
    {
        return $this->certifications ?? [];
    }
}
