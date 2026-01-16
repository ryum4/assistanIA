<?php

namespace App\Service;

/**
 * Service Mock pour tester l'application sans appels à l'API IA
 */
class MockAIService
{
    /**
     * Génère un plan de cours complet (version mock)
     */
    public function generateCoursePlan(
        string $syllabusText,
        int $numberSessions = 8,
        int $durationPerSession = 3,
        string $studentLevel = 'intermédiaire',
        array $competences = []
    ): array {
        return [
            'success' => true,
            'sessions' => [
                [
                    'number' => 1,
                    'title' => 'Introduction au sujet',
                    'duration' => $durationPerSession,
                    'objectives' => ['Comprendre les concepts fondamentaux', 'Établir les bases'],
                    'content' => 'Introduction détaillée au sujet principal du cours.',
                    'exercises' => ['Exercice 1', 'Exercice 2']
                ],
                [
                    'number' => 2,
                    'title' => 'Concepts avancés',
                    'duration' => $durationPerSession,
                    'objectives' => ['Approfondir les connaissances'],
                    'content' => 'Exploration des concepts plus complexes.',
                    'exercises' => ['Exercice 3', 'Exercice 4']
                ]
            ],
            'message' => 'Plan de cours généré avec succès (Mock)'
        ];
    }

    /**
     * Génère des exercices pour une session (version mock)
     */
    public function generateExercises(
        string $sessionContent,
        string $difficulty = 'moyen',
        array $targetCompetences = [],
        string $sessionContext = ''
    ): array {
        return [
            'success' => true,
            'exercises' => [
                [
                    'id' => 1,
                    'title' => 'Exercice de compréhension',
                    'difficulty' => $difficulty,
                    'type' => 'mcq',
                    'question' => 'Question exemple ?',
                    'options' => ['Réponse A', 'Réponse B', 'Réponse C'],
                    'correctAnswer' => 0
                ],
                [
                    'id' => 2,
                    'title' => 'Exercice pratique',
                    'difficulty' => $difficulty,
                    'type' => 'open_question',
                    'question' => 'Expliquez le concept',
                    'expectedContent' => ['Élément 1', 'Élément 2']
                ]
            ],
            'message' => 'Exercices générés avec succès (Mock)'
        ];
    }

    /**
     * Réajuste un plan de cours selon la progression réelle (version mock)
     */
    public function updateCoursePlan(
        string $originalPlan,
        array $realProgress,
        string $studentFeedback = ''
    ): array {
        return [
            'success' => true,
            'updatedPlan' => [
                'adjustments' => ['Ralentissement du rythme recommandé', 'Focus sur les exercices pratiques'],
                'newSchedule' => ['Session 1 étendue à 4 heures', 'Session 2 maintenue']
            ],
            'message' => 'Plan de cours réajusté avec succès (Mock)'
        ];
    }

    /**
     * Chat conversationnel
     */
    public function chat(string $message, ?string $context = null): string
    {
        return "Réponse mock du service IA pour: $message";
    }

    /**
     * Correction d'exercice
     */
    public function correctExercise(string $question, string $studentAnswer, string $expectedAnswer = ''): array
    {
        return [
            'correct' => true,
            'explanation' => 'Correction mock de l\'exercice',
            'improvements' => ['Point d\'amélioration 1'],
            'score' => 85
        ];
    }
}
