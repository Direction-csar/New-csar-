<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthInsuranceSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_nom', 'agent_prenom', 'agent_direction', 'agent_region', 'is_anonymous',
        'q1_info_level', 'q2_documents_clarity', 'q3_difficulty',
        'q4_soins_response', 'q5_panier_soins', 'q6_delais_remboursement',
        'q7_service_client', 'q8_probleme_recent',
        'q9_coassurance', 'q9_autre', 'q10_reseau_soins', 'q10_autre',
        'q11_aspects', 'q11_autre', 'q12_propositions',
        'q13_note',
        'ip_address', 'user_agent', 'submitted_at',
    ];

    protected $casts = [
        'q11_aspects'  => 'array',
        'is_anonymous' => 'boolean',
        'q13_note'     => 'integer',
        'submitted_at' => 'datetime',
    ];

    public static function questionLabels(): array
    {
        return [
            'q1_info_level' => [
                'label'   => 'Le personnel est-il suffisamment informé des modalités ?',
                'options' => ['totalement' => 'Oui, totalement', 'partiellement' => 'Partiellement', 'non' => 'Non'],
            ],
            'q2_documents_clarity' => [
                'label'   => 'Les documents explicatifs sont-ils clairs ?',
                'options' => ['tres_clairs' => 'Très clairs', 'moyennement' => 'Moyennement clairs', 'peu_clairs' => 'Peu clairs'],
            ],
            'q3_difficulty' => [
                'label'   => 'Difficultés à comprendre vos droits et obligations ?',
                'options' => ['jamais' => 'Jamais', 'parfois' => 'Parfois', 'souvent' => 'Souvent'],
            ],
            'q4_soins_response' => [
                'label'   => 'Les soins répondent-ils à vos besoins ?',
                'options' => ['largement' => 'Oui, largement', 'avec_limites' => 'Oui, avec des limites', 'non' => 'Non'],
            ],
            'q5_panier_soins' => [
                'label'   => 'Le panier de soins est-il suffisant ?',
                'options' => ['tres_suffisant' => 'Très suffisant', 'assez_suffisant' => 'Assez suffisant', 'insuffisant' => 'Insuffisant'],
            ],
            'q6_delais_remboursement' => [
                'label'   => 'Les délais de remboursement sont-ils satisfaisants ?',
                'options' => ['rapides' => 'Toujours rapides', 'acceptables' => 'Acceptables', 'longs' => 'Trop longs'],
            ],
            'q7_service_client' => [
                'label'   => 'Le service client est-il de bonne qualité ?',
                'options' => ['oui' => 'Oui', 'non' => 'Non'],
            ],
            'q9_coassurance' => [
                'label'   => 'Taux de coassurance (90%)',
                'options' => ['tres_satisfait' => 'Très satisfait', 'satisfait' => 'Satisfait', 'pas_satisfait' => 'Pas satisfait', 'autre' => 'Autre'],
            ],
            'q10_reseau_soins' => [
                'label'   => 'Le réseau de soins est-il accessible ?',
                'options' => ['tres_accessible' => 'Très accessible', 'accessible' => 'Accessible', 'pas_accessible' => 'Pas accessible', 'autre' => 'Autre'],
            ],
        ];
    }

    public static function aspectOptions(): array
    {
        return [
            'etendue_soins'    => 'Étendue des soins couverts',
            'rapidite_rembour' => 'Rapidité des remboursements',
            'communication'    => 'Communication et information',
            'autre'            => 'Autre',
        ];
    }
}
