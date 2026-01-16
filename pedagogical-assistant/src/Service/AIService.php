<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct(HttpClientInterface $httpClient, string $aiApiKey, string $aiApiUrl, string $aiModel)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $aiApiKey;
        $this->apiUrl = $aiApiUrl;
        $this->model = $aiModel;
    }

    /**
     * Génère un plan de cours complet à partir d'un syllabus
     */
    public function generateCoursePlan(
        string $syllabusText,
        int $numberSessions = 8,
        int $durationPerSession = 3,
        string $studentLevel = 'intermédiaire',
        array $competences = []
    ): array {
        $prompt = $this->buildCoursePlanPrompt(
            $syllabusText,
            $numberSessions,
            $durationPerSession,
            $studentLevel,
            $competences
        );

        $response = $this->callAI($prompt, true);
        
        return $this->parseCoursePlanResponse($response);
    }

    /**
     * Génère des exercices pour une session
     */
    public function generateExercises(
        string $sessionContent,
        string $difficulty = 'moyen',
        array $targetCompetences = [],
        string $sessionContext = ''
    ): array {
        $prompt = $this->buildExercisePrompt($sessionContent, $difficulty, $targetCompetences, $sessionContext);
        $response = $this->callAI($prompt, true);
        
        return $this->parseExercisesResponse($response);
    }

    /**
     * Réajuste un plan de cours selon la progression réelle
     */
    public function updateCoursePlan(
        string $originalPlan,
        array $realProgress,
        string $studentFeedback = ''
    ): array {
        $prompt = $this->buildUpdatePrompt($originalPlan, $realProgress, $studentFeedback);
        $response = $this->callAI($prompt, true);
        
        return $this->parseCoursePlanResponse($response);
    }

    /**
     * Lance une requête à l'API IA
     */
    private function callAI(string $prompt, bool $requireJson = false): string
    {
        try {
            $url = $this->apiUrl;
            // Ajouter /chat/completions si ce n'est pas déjà dans l'URL
            if (strpos($url, '/chat/completions') === false) {
                $url .= '/chat/completions';
            }

            // Définir le système prompt selon le besoin de JSON
            $systemContent = $requireJson 
                ? 'Tu es un assistant pédagogique expert. Réponds toujours en JSON valide.'
                : 'Tu es un assistant pédagogique expert en éducation. Réponds de façon pédagogique, claire et concise.';

            $options = [
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemContent
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 30
            ];

            // Ajouter response_format seulement si c'est OpenAI et JSON requis
            if ($requireJson && strpos($this->apiUrl, 'openai.com') !== false) {
                $options['json']['response_format'] = ['type' => 'json_object'];
            }

            $response = $this->httpClient->request('POST', $url, $options);

            $content = $response->toArray();
            
            if (isset($content['choices'][0]['message']['content'])) {
                return $content['choices'][0]['message']['content'];
            }

            throw new \RuntimeException('Réponse IA invalide');
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'appel IA: ' . $e->getMessage());
        }
    }

    private function buildCoursePlanPrompt(
        string $syllabusText,
        int $numberSessions,
        int $durationPerSession,
        string $studentLevel,
        array $competences
    ): string {
        $competencesStr = implode(', ', $competences) ?: 'Aucune compétence spécifiée';
        
        return <<<PROMPT
Génère un plan de cours complet basé sur le syllabus suivant.

SYLLABUS:
$syllabusText

PARAMÈTRES:
- Nombre de séances: $numberSessions
- Durée par séance: {$durationPerSession}h
- Niveau des étudiants: $studentLevel
- Compétences ciblées: $competencesStr

Retourne un JSON avec la structure suivante:
{
  "plan_general": "description du plan global",
  "seances": [
    {
      "numero": 1,
      "titre": "titre de la séance",
      "objectifs": ["objectif1", "objectif2"],
      "contenus": ["contenu1", "contenu2"],
      "activites": ["activite1", "activite2"],
      "ressources": ["ressource1"]
    }
  ],
  "evaluation": {
    "modalites": ["modalite1"],
    "criteres": ["critere1"]
  }
}
PROMPT;
    }

    private function buildExercisePrompt(
        string $sessionContent,
        string $difficulty,
        array $targetCompetences,
        string $sessionContext
    ): string {
        $competencesStr = implode(', ', $targetCompetences) ?: 'Aucune compétence spécifiée';
        
        return <<<PROMPT
Génère des exercices pédagogiques pour le contenu suivant.

CONTENU DE LA SÉANCE:
$sessionContent

CONTEXTE: $sessionContext

PARAMÈTRES:
- Difficulté: $difficulty
- Compétences ciblées: $competencesStr

Retourne un JSON avec la structure suivante:
{
  "exercises": [
    {
      "titre": "titre de l'exercice",
      "consigne": "description détaillée",
      "type": "type d'exercice",
      "difficulte": "$difficulty",
      "correction": {
        "reponse": "réponse attendue",
        "points_cles": ["point1", "point2"]
      }
    }
  ]
}
PROMPT;
    }

    private function buildUpdatePrompt(
        string $originalPlan,
        array $realProgress,
        string $studentFeedback
    ): string {
        $progressStr = json_encode($realProgress, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return <<<PROMPT
Réajuste le plan de cours suivant en fonction de la progression réelle.

PLAN ORIGINAL:
$originalPlan

PROGRESSION RÉELLE:
$progressStr

RETOURS DES ÉTUDIANTS:
$studentFeedback

Retourne un JSON avec les séances réajustées:
{
  "seances_ajustees": [
    {
      "numero": 1,
      "titre": "titre réajusté",
      "objectifs": ["obj1"],
      "contenus": ["contenu1"],
      "activites": ["activite1"]
    }
  ],
  "notes_ajustements": "explications des changements"
}
PROMPT;
    }

    private function parseCoursePlanResponse(string $jsonResponse): array
    {
        // Nettoyer la réponse si elle contient du markdown
        $jsonResponse = $this->extractJsonFromResponse($jsonResponse);
        
        $data = json_decode($jsonResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Réponse JSON invalide: ' . json_last_error_msg() . ' | Response: ' . substr($jsonResponse, 0, 200));
        }

        return $data ?? [];
    }

    private function parseExercisesResponse(string $jsonResponse): array
    {
        // Nettoyer la réponse si elle contient du markdown
        $jsonResponse = $this->extractJsonFromResponse($jsonResponse);
        
        $data = json_decode($jsonResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Réponse JSON invalide: ' . json_last_error_msg());
        }

        return $data['exercises'] ?? [];
    }

    /**
     * Extrait le JSON d'une réponse qui pourrait contenir du markdown
     */
    private function extractJsonFromResponse(string $response): string
    {
        // Essayer d'abord directement
        $trimmed = trim($response);
        if (json_decode($trimmed, true) !== null) {
            return $trimmed;
        }

        // Chercher du JSON entre ```json et ```
        if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
            $json = trim($matches[1]);
            if (json_decode($json, true) !== null) {
                return $json;
            }
        }

        // Chercher un bloc JSON entre { et }
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = trim($matches[0]);
            if (json_decode($json, true) !== null) {
                return $json;
            }
        }

        // Si rien ne fonctionne, retourner la réponse originale
        return $trimmed;
    }

    /**
     * Chat conversationnel avec l'IA
     */
    public function chat(string $message, ?string $context = null): string
    {
        $prompt = "Vous êtes un assistant pédagogique expert en éducation.\n";
        
        if ($context) {
            $prompt .= "Contexte: $context\n";
        }
        
        $prompt .= "\nQuestion de l'utilisateur: $message\n";
        $prompt .= "\nRépondez de façon pédagogique, claire et concise.";

        return $this->callAI($prompt);
    }

    /**
     * Corrige un exercice automatiquement
     */
    public function correctExercise(string $question, string $studentAnswer, string $expectedAnswer = ''): array
    {
        $prompt = "Vous êtes un correcteur d'exercices expert.\n\n";
        $prompt .= "Question: $question\n";
        $prompt .= "Réponse de l'étudiant: $studentAnswer\n";
        
        if ($expectedAnswer) {
            $prompt .= "Réponse attendue: $expectedAnswer\n";
        }
        
        $prompt .= "\nFournissez:\n";
        $prompt .= "1. Si la réponse est correcte (true/false)\n";
        $prompt .= "2. Explication détaillée\n";
        $prompt .= "3. Points d'amélioration\n";
        $prompt .= "4. Score (0-100)\n";
        $prompt .= "\nRépondez en JSON: {\"correct\": bool, \"explanation\": string, \"improvements\": [string], \"score\": number}";

        $response = $this->callAI($prompt);
        
        $data = json_decode($response, true);
        if (!$data) {
            return [
                'correct' => false,
                'explanation' => $response,
                'improvements' => [],
                'score' => 0
            ];
        }

        return $data;
    }
}
