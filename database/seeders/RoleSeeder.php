<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Accès complet à toutes les fonctionnalités'
            ],
            [
                'name' => 'dg',
                'display_name' => 'Directeur Général',
                'description' => 'Accès en lecture seule à toutes les données'
            ],
            [
                'name' => 'responsable',
                'display_name' => 'Responsable Entrepôt',
                'description' => 'Gestion des stocks et entrepôts'
            ],
            [
                'name' => 'agent',
                'display_name' => 'Agent CSAR',
                'description' => 'Accès limité en lecture seule'
            ],
            [
                'name' => 'ctc',
                'display_name' => 'Conseil Technique de la Communication',
                'description' => 'Gestion des publications, actualités, rapports, newsletter et contenu public'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
} 