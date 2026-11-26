<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\About;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        About::create([
            'official_name' => 'Comité de Secours et d\'Assistance aux Réfugiés',
            'acronym' => 'CSAR',
            'founded_year' => 2010,
            'headquarters' => 'Dakar, Sénégal',
            'phone' => '+221 77 645 92 42',
            'email' => 'contact@csar.sn',
            'website' => 'https://www.csar.sn',
            'mission' => 'Le CSAR s\'engage à fournir une assistance humanitaire d\'urgence et des services de secours aux réfugiés et aux populations vulnérables au Sénégal et dans la région. Notre mission est de sauver des vies, d\'atténuer les souffrances et de protéger la dignité humaine dans les situations de crise.',
            'vision' => 'Nous aspirons à un monde où chaque personne déplacée ou réfugiée a accès à une assistance humanitaire de qualité, à la protection et à des solutions durables. Le CSAR vise à devenir un acteur de référence dans la réponse humanitaire en Afrique de l\'Ouest.',
            'values' => 'Humanité, Neutralité, Impartialité, Indépendance, Volontariat, Unité, Universalité',
            'leadership' => [
                [
                    'name' => 'Dr. Aminata Diallo',
                    'position' => 'Directrice Générale',
                    'email' => 'dg@csar.sn'
                ],
                [
                    'name' => 'M. Ousmane Ndiaye',
                    'position' => 'Directeur des Opérations',
                    'email' => 'operations@csar.sn'
                ],
                [
                    'name' => 'Mme. Fatou Sarr',
                    'position' => 'Directrice des Ressources Humaines',
                    'email' => 'rh@csar.sn'
                ]
            ],
            'partners' => [
                [
                    'name' => 'Haut Commissariat des Nations Unies pour les Réfugiés (HCR)',
                    'description' => 'Partenariat stratégique pour la protection des réfugiés',
                    'website' => 'https://www.unhcr.org'
                ],
                [
                    'name' => 'Organisation Internationale pour les Migrations (OIM)',
                    'description' => 'Coopération pour la gestion des migrations',
                    'website' => 'https://www.iom.int'
                ],
                [
                    'name' => 'Croix-Rouge Sénégalaise',
                    'description' => 'Partenariat local pour l\'assistance humanitaire',
                    'website' => 'https://www.croix-rouge.sn'
                ]
            ],
            'certifications' => [
                [
                    'name' => 'ISO 9001:2015',
                    'description' => 'Système de management de la qualité',
                    'issuer' => 'Organisation Internationale de Normalisation',
                    'issue_date' => '2023-01-15'
                ],
                [
                    'name' => 'Certification HCR',
                    'description' => 'Partenariat opérationnel avec le HCR',
                    'issuer' => 'Haut Commissariat des Nations Unies pour les Réfugiés',
                    'issue_date' => '2022-06-20'
                ]
            ],
            'is_active' => true
        ]);
    }
}