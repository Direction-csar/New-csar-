<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use Carbon\Carbon;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualité 1 : Retraite stratégique CSAR 2025
        News::create([
            'title' => 'Retraite stratégique CSAR 2025 : Gouvernance, Performance et Leadership',
            'slug' => 'retraite-strategique-csar-2025',
            'excerpt' => 'Le CSAR a tenu sa Retraite stratégique du 21 au 23 décembre 2025 autour du thème « Gouvernance, Performance et Leadership au service de la Sécurité Alimentaire et de la Résilience ».',
            'content' => '<div style="line-height: 1.8;">
                <p><strong>#TeamBuilding | Retraite stratégique CSAR 2025</strong></p>
                
                <p>Le Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR) a tenu, du 21 au 23 décembre 2025, sa Retraite stratégique autour du thème :</p>
                
                <p style="text-align: center; font-size: 1.2rem; font-weight: 600; color: #22c55e; margin: 20px 0;">
                    « Gouvernance, Performance et Leadership au service de la Sécurité Alimentaire et de la Résilience »
                </p>
                
                <p>Cette retraite a constitué un cadre structurant de réflexion, de partage et de renforcement des capacités, visant notamment à :</p>
                
                <ul style="margin: 20px 0; padding-left: 30px;">
                    <li>Partager et valoriser l\'historique, les missions ainsi que le positionnement stratégique du CSAR</li>
                    <li>Renforcer la gouvernance administrative et le pilotage opérationnel</li>
                    <li>Améliorer les pratiques de management des ressources humaines</li>
                    <li>Aligner les interventions du CSAR avec la Lettre de Politique Sectorielle et de Développement (LPSD) du MIFASS et la Vision Sénégal 2050</li>
                    <li>Promouvoir une culture de qualité, de performance, de transparence et de responsabilité</li>
                    <li>Développer le leadership collectif et la cohésion des équipes</li>
                </ul>
                
                <p>Les travaux ont réuni la Direction Générale et les Inspecteurs Régionaux du CSAR, ainsi que des partenaires institutionnels et personnes ressources, autour de sessions plénières, d\'ateliers thématiques et d\'activités de cohésion.</p>
                
                <p style="font-weight: 600; margin-top: 30px;">Cette retraite marque une étape importante dans la consolidation du CSAR en tant qu\'acteur stratégique de la sécurité alimentaire et de la résilience au Sénégal.</p>
                
                <p style="margin-top: 30px;">
                    <strong>Hashtags :</strong> #CSAR #SécuritéAlimentaire #Résilience #Gouvernance #Performance #Leadership #TeamCSAR #Sénégal2050
                </p>
                
                <div style="margin-top: 40px; padding: 20px; background: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 8px;">
                    <h4 style="color: #166534; margin-bottom: 10px;">📄 Documents disponibles</h4>
                    <p><a href="/rapport/Rapport%20Annuel%20CSAR2024%20VF.pdf" target="_blank" style="color: #22c55e; text-decoration: underline;">📥 Télécharger le Rapport Annuel CSAR 2024</a></p>
                    <p><a href="/rapport/BILAN%20SOCIAL%20CSAR2024%20VF.pdf" target="_blank" style="color: #22c55e; text-decoration: underline;">📥 Télécharger le Bilan Social CSAR 2024</a></p>
                </div>
            </div>',
            'featured_image' => 'images/bloc/csar14.jpg',
            'category' => 'Événements',
            'status' => 'published',
            'is_published' => true,
            'is_featured' => true,
            'is_public' => true,
            'published_at' => Carbon::now()->subMonths(2),
            'views_count' => 245,
            'created_by' => 1,
        ]);

        // Actualité 2 : Renforcement SIM avec vidéo YouTube
        News::create([
            'title' => 'Renforcement et modernisation du Système d\'Information des Marchés (SIM)',
            'slug' => 'renforcement-sim-2025',
            'excerpt' => 'Atelier national de renforcement de capacités et d\'actualisation du SIM du 19 au 20 décembre 2025 à l\'Hôtel Royal Saly dans le cadre du partenariat CSAR–FSRP.',
            'content' => '<div style="line-height: 1.8;">
                <p><strong>Renforcement et modernisation du Système d\'Information des Marchés (SIM) du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong></p>
                
                <p>Dans le cadre du partenariat <strong>#CSAR–FSRP SENEGAL</strong> et à travers la mise en œuvre du Plan d\'action 2025, un atelier national de renforcement de capacités et d\'actualisation du SIM s\'est tenu du 19 au 20 décembre 2025 à l\'Hôtel Royal Saly.</p>
                
                <h3 style="color: #22c55e; margin-top: 30px;">🎯 Objectif</h3>
                <p>Améliorer la fiabilité, la couverture et l\'efficacité du dispositif national de collecte et d\'analyse des données de marché pour une meilleure aide à la décision en matière de sécurité alimentaire et de résilience.</p>
                
                <h3 style="color: #22c55e; margin-top: 30px;">✨ Avancées clés</h3>
                <ul style="margin: 20px 0; padding-left: 30px;">
                    <li><strong>Extension du SIM</strong> de 55 à 86 marchés couvrant les 46 départements du pays</li>
                    <li><strong>Élargissement du panier</strong> de 32 à 50 denrées</li>
                    <li><strong>Validation</strong> d\'une nouvelle fiche SIM harmonisée</li>
                    <li><strong>Harmonisation</strong> de la méthodologie de collecte et de supervision</li>
                    <li><strong>Renforcement des capacités</strong> des agents du CSAR (direction générale et inspections régionales)</li>
                </ul>
                
                <h3 style="color: #22c55e; margin-top: 30px;">👥 Participants</h3>
                <p>Direction générale et Inspections régionales du CSAR, Partenaire Technique et Financier (FSRP).</p>
                
                <p style="font-weight: 600; margin-top: 30px;">Cette activité constitue une étape majeure vers la digitalisation du SIM, l\'amélioration du bulletin mensuel des marchés et une anticipation renforcée des risques d\'insécurité alimentaire au Sénégal.</p>
                
                <div style="margin-top: 40px; padding: 20px; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 8px;">
                    <h4 style="color: #1e40af; margin-bottom: 15px;">🎥 Vidéo de présentation</h4>
                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                        <iframe width="100%" height="100%" src="https://www.youtube.com/embed/OjANwvxMQCA" 
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    </div>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 8px;">
                    <h4 style="color: #166534; margin-bottom: 10px;">📄 Documents disponibles</h4>
                    <p><a href="/sim/SIM%20Juillet%202025.pdf" target="_blank" style="color: #22c55e; text-decoration: underline;">📥 Télécharger le Bulletin SIM Juillet 2025</a></p>
                </div>
                
                <p style="margin-top: 30px;">
                    <strong>Hashtags :</strong> #CSAR #SécuritéAlimentaire #Résilience #SIM #FSRP #Sénégal #Vision2050
                </p>
                
                <p style="margin-top: 20px; font-style: italic; color: #6b7280;">
                    Partenaires : Ministère de la Famille de l\'Action sociale et des Solidarités -Sénégal | Ministère de l\'Agriculture, de la Souveraineté Alimentaire et de l\'Élevage MASAE | The World Bank
                </p>
            </div>',
            'featured_image' => 'images/bloc/csar19.jpg',
            'youtube_url' => 'https://youtu.be/OjANwvxMQCA?si=NQzmxZB7UJLKWbLT',
            'category' => 'Projets',
            'status' => 'published',
            'is_published' => true,
            'is_featured' => true,
            'is_public' => true,
            'published_at' => Carbon::now()->subMonths(2),
            'views_count' => 312,
            'created_by' => 1,
        ]);

        // Ajouter des commentaires pour ces actualités
        $this->addComments();
    }

    private function addComments()
    {
        // Vous pouvez ajouter des commentaires ici si vous avez un système de commentaires
        // Pour l'instant, je laisse cette méthode vide
    }
}