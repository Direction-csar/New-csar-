<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;
use Carbon\Carbon;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Témoignages de mission approuvés
        Testimonial::create([
            'name' => 'Amadou Diallo',
            'email' => 'amadou.diallo@csar.sn',
            'organization' => 'Équipe Logistique CSAR',
            'type' => 'mission',
            'mission_location' => 'Région de Kaolack',
            'mission_date' => Carbon::parse('2024-11-15'),
            'message' => 'Mission très enrichissante auprès des populations de Kaolack. Nous avons pu distribuer 5 tonnes de riz et sensibiliser plus de 200 familles sur la sécurité alimentaire. L\'accueil chaleureux des communautés locales nous a profondément touchés.',
            'status' => 'approved',
            'rating' => 5,
            'is_featured' => true,
        ]);

        Testimonial::create([
            'name' => 'Fatou Sall',
            'email' => 'fatou.sall@csar.sn',
            'organization' => 'Coordinatrice Régionale',
            'type' => 'mission',
            'mission_location' => 'Région de Saint-Louis',
            'mission_date' => Carbon::parse('2024-10-20'),
            'message' => 'Une expérience inoubliable dans la région de Saint-Louis. Les équipes locales ont fait un travail remarquable. Nous avons réussi à mettre en place un système d\'alerte précoce qui va bénéficier à plus de 15 000 personnes.',
            'status' => 'approved',
            'rating' => 5,
            'is_featured' => true,
        ]);

        Testimonial::create([
            'name' => 'Moussa Ndiaye',
            'email' => 'moussa.ndiaye@csar.sn',
            'organization' => 'Agent de Terrain',
            'type' => 'mission',
            'mission_location' => 'Région de Tambacounda',
            'mission_date' => Carbon::parse('2024-09-10'),
            'message' => 'Mission de résilience dans la région de Tambacounda. Nous avons formé 50 agriculteurs aux techniques de conservation des sols. La reconnaissance et la gratitude des bénéficiaires nous motivent à continuer notre engagement.',
            'status' => 'approved',
            'rating' => 4,
            'is_featured' => false,
        ]);

        Testimonial::create([
            'name' => 'Aïssatou Ba',
            'email' => 'aissatou.ba@csar.sn',
            'organization' => 'Responsable Nutrition',
            'type' => 'mission',
            'mission_location' => 'Région de Kolda',
            'mission_date' => Carbon::parse('2024-12-05'),
            'message' => 'Programme nutritionnel à Kolda : nous avons dépistré et pris en charge 120 enfants souffrant de malnutrition. Voir les sourires des mères et la transformation des enfants est la plus belle récompense de notre travail.',
            'status' => 'approved',
            'rating' => 5,
            'is_featured' => true,
        ]);

        Testimonial::create([
            'name' => 'Ibrahima Sarr',
            'email' => 'ibrahima.sarr@csar.sn',
            'organization' => 'Chef de Mission',
            'type' => 'mission',
            'mission_location' => 'Région de Ziguinchor',
            'mission_date' => Carbon::parse('2024-08-25'),
            'message' => 'Intervention d\'urgence suite aux inondations à Ziguinchor. En 48h, nous avons mobilisé 3 tonnes de vivres et 500 kits d\'hygiène. La solidarité de l\'équipe et la résilience des populations nous ont profondément marqués.',
            'status' => 'approved',
            'rating' => 5,
            'is_featured' => false,
        ]);

        Testimonial::create([
            'name' => 'Mariama Diop',
            'email' => 'mariama.diop@csar.sn',
            'organization' => 'Assistante Sociale',
            'type' => 'mission',
            'mission_location' => 'Région de Thiès',
            'mission_date' => Carbon::parse('2025-01-15'),
            'message' => 'Mission de sensibilisation à Thiès sur les bonnes pratiques alimentaires. Plus de 300 femmes ont participé aux ateliers. Leur engagement et leur volonté d\'améliorer la nutrition de leurs familles sont inspirants.',
            'status' => 'approved',
            'rating' => 4,
            'is_featured' => false,
        ]);

        // Témoignage en attente de validation
        Testimonial::create([
            'name' => 'Ousmane Fall',
            'email' => 'ousmane.fall@csar.sn',
            'organization' => 'Logisticien',
            'type' => 'mission',
            'mission_location' => 'Région de Louga',
            'mission_date' => Carbon::parse('2025-02-01'),
            'message' => 'Mission de distribution de semences à Louga. Excellente coordination avec les autorités locales. Les agriculteurs sont très reconnaissants du soutien apporté.',
            'status' => 'pending',
            'rating' => 5,
            'is_featured' => false,
        ]);

        // Témoignages généraux (pour la page d'accueil)
        Testimonial::create([
            'name' => 'Cheikh Sy',
            'email' => 'cheikh.sy@example.com',
            'organization' => 'Bénéficiaire',
            'type' => 'general',
            'mission_location' => null,
            'mission_date' => null,
            'message' => 'Le CSAR a changé la vie de ma famille. Grâce à leur soutien, nous avons pu surmonter une période difficile. Merci infiniment pour votre engagement auprès des populations vulnérables.',
            'status' => 'approved',
            'rating' => 5,
            'is_featured' => true,
        ]);

        Testimonial::create([
            'name' => 'Awa Gueye',
            'email' => 'awa.gueye@example.com',
            'organization' => 'Partenaire ONG',
            'type' => 'general',
            'mission_location' => null,
            'mission_date' => null,
            'message' => 'Travailler avec le CSAR est un véritable plaisir. Leur professionnalisme et leur dévouement sont exemplaires. Ensemble, nous faisons la différence pour la sécurité alimentaire au Sénégal.',
            'status' => 'approved',
            'rating' => 5,
            'is_featured' => true,
        ]);
    }
}
