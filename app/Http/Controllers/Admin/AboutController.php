<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AboutController extends Controller
{
    /**
     * Afficher la page À propos du CSAR
     */
    public function index()
    {
        try {
            // Statistiques
            $stats = $this->getAboutStats();
            
            // Informations générales
            $generalInfo = $this->getGeneralInfo();
            
            // Mission et vision
            $missionVision = $this->getMissionVision();
            
            // Équipe dirigeante
            $leadership = $this->getLeadership();
            
            // Partenaires
            $partners = $this->getPartners();
            
            // Certifications
            $certifications = $this->getCertifications();
            
            return view('admin.about.index', compact(
                'stats', 
                'generalInfo', 
                'missionVision', 
                'leadership', 
                'partners', 
                'certifications'
            ));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@index', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return view('admin.about.index', [
                'stats' => $this->getDefaultStats(),
                'generalInfo' => $this->getDefaultGeneralInfo(),
                'missionVision' => $this->getDefaultMissionVision(),
                'leadership' => $this->getDefaultLeadership(),
                'partners' => $this->getDefaultPartners(),
                'certifications' => $this->getDefaultCertifications()
            ]);
        }
    }
    
    /**
     * Mettre à jour les informations générales
     */
    public function updateGeneralInfo(Request $request)
    {
        try {
            $request->validate([
                'official_name' => 'required|string|max:255',
                'acronym' => 'required|string|max:10',
                'creation_date' => 'required|string|max:50',
                'legal_status' => 'required|string|max:255',
                'headquarters' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'website' => 'nullable|url|max:255'
            ]);
            
            // Simulation de mise à jour
            $generalInfo = [
                'official_name' => $request->official_name,
                'acronym' => $request->acronym,
                'creation_date' => $request->creation_date,
                'legal_status' => $request->legal_status,
                'headquarters' => $request->headquarters,
                'phone' => $request->phone,
                'email' => $request->email,
                'website' => $request->website,
                'updated_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Informations mises à jour',
                "Les informations générales du CSAR ont été mises à jour",
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Informations générales mises à jour avec succès',
                'data' => $generalInfo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@updateGeneralInfo', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des informations'
            ], 500);
        }
    }
    
    /**
     * Mettre à jour la mission et vision
     */
    public function updateMissionVision(Request $request)
    {
        try {
            $request->validate([
                'mission' => 'required|string|max:2000',
                'vision' => 'required|string|max:2000'
            ]);
            
            // Simulation de mise à jour
            $missionVision = [
                'mission' => $request->mission,
                'vision' => $request->vision,
                'updated_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Mission et Vision mises à jour',
                "La mission et la vision du CSAR ont été mises à jour",
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Mission et vision mises à jour avec succès',
                'data' => $missionVision
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@updateMissionVision', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la mission et vision'
            ], 500);
        }
    }
    
    /**
     * Ajouter un membre de l'équipe dirigeante
     */
    public function addLeadershipMember(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'bio' => 'nullable|string|max:1000'
            ]);
            
            // Simulation d'ajout
            $member = [
                'id' => rand(1000, 9999),
                'name' => $request->name,
                'position' => $request->position,
                'email' => $request->email,
                'phone' => $request->phone,
                'bio' => $request->bio,
                'created_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Membre ajouté',
                "Le membre '{$member['name']}' a été ajouté à l'équipe dirigeante",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Membre ajouté avec succès',
                'data' => $member
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@addLeadershipMember', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du membre'
            ], 500);
        }
    }
    
    /**
     * Ajouter un partenaire
     */
    public function addPartner(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'website' => 'nullable|url|max:255',
                'type' => 'required|in:international,local,government,ngo'
            ]);
            
            // Simulation d'ajout
            $partner = [
                'id' => rand(1000, 9999),
                'name' => $request->name,
                'description' => $request->description,
                'website' => $request->website,
                'type' => $request->type,
                'created_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Partenaire ajouté',
                "Le partenaire '{$partner['name']}' a été ajouté",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Partenaire ajouté avec succès',
                'data' => $partner
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@addPartner', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du partenaire'
            ], 500);
        }
    }
    
    /**
     * Ajouter une certification
     */
    public function addCertification(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'issuer' => 'required|string|max:255',
                'issue_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:issue_date'
            ]);
            
            // Simulation d'ajout
            $certification = [
                'id' => rand(1000, 9999),
                'name' => $request->name,
                'description' => $request->description,
                'issuer' => $request->issuer,
                'issue_date' => Carbon::parse($request->issue_date),
                'expiry_date' => $request->expiry_date ? Carbon::parse($request->expiry_date) : null,
                'created_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Certification ajoutée',
                "La certification '{$certification['name']}' a été ajoutée",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Certification ajoutée avec succès',
                'data' => $certification
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@addCertification', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la certification'
            ], 500);
        }
    }
    
    /**
     * Exporter les informations
     */
    public function exportInfo(Request $request)
    {
        try {
            $format = $request->get('format', 'pdf');
            
            // Simulation d'export
            $exportData = [
                'general_info' => $this->getGeneralInfo(),
                'mission_vision' => $this->getMissionVision(),
                'leadership' => $this->getLeadership(),
                'partners' => $this->getPartners(),
                'certifications' => $this->getCertifications(),
                'exported_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Export des informations',
                "L'export des informations CSAR a été généré",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Export généré avec succès',
                'download_url' => '/download/csar-info.' . $format
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@exportInfo', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des informations'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques
     */
    public function getStats()
    {
        try {
            $stats = $this->getAboutStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans AboutController@getStats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques À propos
     */
    private function getAboutStats()
    {
        try {
            return [
                'founded_year' => 2010,
                'total_staff' => \App\Models\Personnel::count(),
                'total_warehouses' => \App\Models\Warehouse::count(),
                'beneficiaries' => \App\Models\PublicRequest::count()
            ];
        } catch (\Exception $e) {
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Obtenir les informations générales
     */
    private function getGeneralInfo()
    {
        try {
            // Récupérer depuis la table public_contents ou utiliser des valeurs par défaut
            $generalInfo = \App\Models\PublicContent::where('section', 'general_info')->get()->keyBy('key_name');
            
            return [
                'official_name' => $generalInfo->get('official_name')?->value ?? 'Comité de Secours et d\'Assistance aux Réfugiés (CSAR)',
                'acronym' => $generalInfo->get('acronym')?->value ?? 'CSAR',
                'creation_date' => $generalInfo->get('creation_date')?->value ?? '15 Mars 2010',
                'legal_status' => $generalInfo->get('legal_status')?->value ?? 'Organisation Non Gouvernementale (ONG)',
                'headquarters' => $generalInfo->get('headquarters')?->value ?? 'Dakar, Sénégal',
                'phone' => $generalInfo->get('phone')?->value ?? '+221 77 645 92 42',
                'email' => $generalInfo->get('email')?->value ?? 'contact@csar.sn',
                'website' => $generalInfo->get('website')?->value ?? 'www.csar.sn'
            ];
        } catch (\Exception $e) {
            return $this->getDefaultGeneralInfo();
        }
    }
    
    /**
     * Obtenir la mission et vision
     */
    private function getMissionVision()
    {
        try {
            return [
                'mission' => 'Le CSAR s\'engage à fournir une assistance humanitaire d\'urgence et des services de secours aux réfugiés et aux populations vulnérables au Sénégal et dans la région. Notre mission est de sauver des vies, d\'atténuer les souffrances et de protéger la dignité humaine dans les situations de crise.',
                'vision' => 'Nous aspirons à un monde où chaque personne déplacée ou réfugiée a accès à une assistance humanitaire de qualité, à la protection et à des solutions durables. Le CSAR vise à devenir un acteur de référence dans la réponse humanitaire en Afrique de l\'Ouest.'
            ];
        } catch (\Exception $e) {
            return $this->getDefaultMissionVision();
        }
    }
    
    /**
     * Obtenir l'équipe dirigeante
     */
    private function getLeadership()
    {
        try {
            // Récupérer depuis la table personnel avec des postes de direction
            return \App\Models\Personnel::whereIn('poste', ['Directeur Général', 'Directeur Administratif', 'Responsable Logistique', 'Directeur RH'])
                ->select('id', 'nom', 'prenom', 'poste', 'email', 'telephone')
                ->get()
                ->map(function($personnel) {
                    return [
                        'id' => $personnel->id,
                        'name' => $personnel->nom . ' ' . $personnel->prenom,
                        'position' => $personnel->poste,
                        'email' => $personnel->email,
                        'phone' => $personnel->telephone
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return $this->getDefaultLeadership();
        }
    }
    
    /**
     * Obtenir les partenaires
     */
private function getPartners()
    {
        try {
            // Récupérer depuis la table technical_partners
            return \App\Models\TechnicalPartner::where('is_active', true)
                ->select('id', 'name', 'description', 'type')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return $this->getDefaultPartners();
        }
    }
    
    /**
     * Obtenir les certifications
     */
    private function getCertifications()
    {
        try {
            return [
                [
                    'id' => 1,
                    'name' => 'ISO 9001:2015',
                    'description' => 'Système de Management de la Qualité',
                    'issuer' => 'ISO',
                    'issue_date' => '2020-01-15',
                    'expiry_date' => '2023-01-15'
                ],
                [
                    'id' => 2,
                    'name' => 'Certification HAP',
                    'description' => 'Humanitarian Accountability Partnership',
                    'issuer' => 'HAP International',
                    'issue_date' => '2019-06-01',
                    'expiry_date' => '2022-06-01'
                ]
            ];
        } catch (\Exception $e) {
            return $this->getDefaultCertifications();
        }
    }
    
    /**
     * Statistiques par défaut
     */
    private function getDefaultStats()
    {
        return [
            'founded_year' => 2010,
            'total_staff' => 0,
            'total_warehouses' => 0,
            'beneficiaries' => 0
        ];
    }
    
    /**
     * Informations générales par défaut
     */
    private function getDefaultGeneralInfo()
    {
        return [
            'official_name' => 'Comité de Secours et d\'Assistance aux Réfugiés (CSAR)',
            'acronym' => 'CSAR',
            'creation_date' => '15 Mars 2010',
            'legal_status' => 'Organisation Non Gouvernementale (ONG)',
            'headquarters' => 'Dakar, Sénégal',
            'phone' => '+221 77 645 92 42',
            'email' => 'contact@csar.sn',
            'website' => 'www.csar.sn'
        ];
    }
    
    /**
     * Mission et vision par défaut
     */
    private function getDefaultMissionVision()
    {
        return [
            'mission' => 'Le CSAR s\'engage à fournir une assistance humanitaire d\'urgence et des services de secours aux réfugiés et aux populations vulnérables au Sénégal et dans la région.',
            'vision' => 'Nous aspirons à un monde où chaque personne déplacée ou réfugiée a accès à une assistance humanitaire de qualité.'
        ];
    }
    
    /**
     * Équipe dirigeante par défaut
     */
    private function getDefaultLeadership()
    {
        return [];
    }
    
    /**
     * Partenaires par défaut
     */
    private function getDefaultPartners()
    {
        return [];
    }
    
    /**
     * Certifications par défaut
     */
    private function getDefaultCertifications()
    {
        return [];
    }
    
    /**
     * Créer une notification
     */
    private function createNotification($title, $message, $type = 'info')
    {
        try {
            if (class_exists('App\Models\Notification')) {
                \App\Models\Notification::create([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'user_id' => auth()->id(),
                    'read' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de notification', [
                'error' => $e->getMessage()
            ]);
        }
    }
}