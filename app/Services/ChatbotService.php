<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private string $provider;
    private string $model;
    private ?string $apiKey;

    public function __construct()
    {
        $this->provider = config('services.chatbot.provider', 'gemini');
        $this->model = config('services.chatbot.model', 'gemini-2.0-flash');
        $this->apiKey = config('services.chatbot.api_key');
    }

    /**
     * Generate a response from the AI chatbot
     */
    public function ask(string $question, array $history = []): array
    {
        if (!$this->apiKey) {
            return $this->fallbackResponse($question);
        }

        try {
            $systemPrompt = $this->getSystemPrompt();
            $response = match ($this->provider) {
                'gemini' => $this->askGemini($question, $history, $systemPrompt),
                'openai' => $this->askOpenAI($question, $history, $systemPrompt),
                default => $this->fallbackResponse($question),
            };

            return [
                'success' => true,
                'message' => $response,
                'source' => $this->provider,
            ];
        } catch (\Exception $e) {
            Log::warning('Chatbot AI error, using fallback', [
                'error' => $e->getMessage(),
                'provider' => $this->provider,
            ]);

            return $this->fallbackResponse($question);
        }
    }

    /**
     * Call Gemini API (free tier: gemini-2.0-flash)
     */
    private function askGemini(string $question, array $history, string $systemPrompt): string
    {
        $contents = [];

        // System instruction
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => "Compris. Je suis l'Assistant CSAR. Je réponds en français, de manière concise et utile."]],
        ];

        // Conversation history (last 6 messages max)
        foreach (array_slice($history, -6) as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // Current question
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $question]],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(15)->post($url, [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500,
                'topP' => 0.9,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini API error: ' . $response->status());
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text']
            ?? throw new \Exception('Empty Gemini response');
    }

    /**
     * Call OpenAI API
     */
    private function askOpenAI(string $question, array $history, string $systemPrompt): string
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach (array_slice($history, -6) as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        $response = Http::timeout(15)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->status());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content']
            ?? throw new \Exception('Empty OpenAI response');
    }

    /**
     * System prompt with CSAR knowledge base
     */
    private function getSystemPrompt(): string
    {
        $knowledge = $this->getKnowledgeBase();

        return <<<PROMPT
Tu es l'Assistant du CSAR (Commissariat à la Sécurité Alimentaire et à la Résilience) du Sénégal.

RÈGLES :
- Réponds TOUJOURS en français, de manière concise (3-5 phrases max).
- Ne donne JAMAIS d'informations inventées. Si tu ne sais pas, oriente vers le contact.
- Utilise un ton professionnel mais chaleureux.
- Formate les liens en HTML : <a href="URL">texte</a>
- Pour les listes, utilise <ul><li>...</li></ul>
- Ne répète pas la question de l'utilisateur.

BASE DE CONNAISSANCES CSAR :
{$knowledge}

Si la question sort du périmètre CSAR, dis poliment que tu ne peux répondre qu'aux questions liées au CSAR et oriente vers la page Contact.
PROMPT;
    }

    /**
     * Knowledge base about CSAR
     */
    private function getKnowledgeBase(): string
    {
        return Cache::remember('chatbot_knowledge', 3600, function () {
            return <<<'KB'
## À PROPOS DU CSAR
Le CSAR (Commissariat à la Sécurité Alimentaire et à la Résilience) est un organisme public sénégalais rattaché à la Présidence de la République. Sa mission : prévenir et gérer les crises alimentaires, renforcer la résilience des populations vulnérables.

## MISSIONS PRINCIPALES
- Coordination de la réponse aux crises alimentaires
- Gestion des stocks alimentaires d'urgence
- Système d'Information sur les Marchés (SIM) : suivi des prix des denrées
- Aide alimentaire et distributions ciblées
- Partenariats avec les organisations internationales (PAM, FAO, etc.)

## SERVICES EN LIGNE (pages du site)
- Demande d'aide : formulaire en ligne pour les citoyens nécessitant une assistance
- Suivi de demande : vérifier l'état de votre dossier avec un code de suivi
- Faire un don : contribuer aux actions du CSAR (Wave, Orange Money, carte bancaire, PayPal)
- Rapports SIM : prix des marchés, consultations, carte des marchés
- Actualités : communiqués, événements, projets en cours
- FAQ : questions fréquentes
- Contact : formulaire, email (contact@csar.sn), téléphone

## PROCÉDURE DE DEMANDE D'AIDE
1. Remplir le formulaire (nom, prénom, téléphone, situation, type d'aide)
2. Recevoir un code de suivi par SMS/email
3. Suivre l'avancement sur la page "Suivre ma demande"
4. Être contacté par les équipes CSAR pour l'intervention

## CONTACT
- Site web : https://csar.sn
- Email : contact@csar.sn
- Téléphone : +221 33 XXX XX XX
- Adresse : Dakar, Sénégal

## TYPES D'AIDE DISPONIBLES
- Aide alimentaire d'urgence
- Aide aux sinistrés (inondations, sécheresse)
- Partenariat institutionnel
- Demande d'audience avec la direction
KB;
        });
    }

    /**
     * Fallback response using keyword matching (no API needed)
     */
    private function fallbackResponse(string $question): array
    {
        $q = mb_strtolower(trim($question));

        $patterns = [
            ['keys' => ['demande', 'aide', 'assistance', 'formulaire', 'déposer'],
             'msg' => "Pour effectuer une demande d'aide, rendez-vous sur le formulaire en ligne. Vous recevrez un code de suivi par SMS/email pour suivre votre dossier."],
            ['keys' => ['don', 'donner', 'soutien', 'contribuer'],
             'msg' => "Vous pouvez soutenir le CSAR via Wave, Orange Money, carte bancaire ou PayPal sur la page « Faire un don »."],
            ['keys' => ['suivi', 'suivre', 'code', 'dossier', 'état'],
             'msg' => "Utilisez votre code de suivi sur la page « Suivre ma demande » pour connaître l'état de votre dossier."],
            ['keys' => ['contact', 'email', 'téléphone', 'joindre'],
             'msg' => "Contactez-nous via le formulaire de contact, par email à contact@csar.sn, ou par téléphone."],
            ['keys' => ['prix', 'marché', 'sim', 'rapport'],
             'msg' => "Les rapports SIM (Système d'Information sur les Marchés) sont disponibles sur la page Rapports SIM avec les prix actualisés des denrées."],
            ['keys' => ['faq', 'question'],
             'msg' => "Consultez notre FAQ pour les réponses aux questions les plus fréquentes."],
        ];

        foreach ($patterns as $p) {
            foreach ($p['keys'] as $key) {
                if (str_contains($q, $key)) {
                    return ['success' => true, 'message' => $p['msg'], 'source' => 'fallback'];
                }
            }
        }

        return [
            'success' => true,
            'message' => "Je n'ai pas bien compris votre question. Vous pouvez reformuler ou utiliser les boutons d'action rapide ci-dessus. Pour une aide personnalisée, contactez-nous à contact@csar.sn.",
            'source' => 'fallback',
        ];
    }
}
