<?php

namespace App\Controller;

use App\Entity\CoursePlan;
use App\Entity\Session;
use App\Entity\Syllabus;
use App\Repository\SyllabusRepository;
use App\Service\AIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class AIController extends AbstractController
{
    #[Route('/generate-course-plan', name: 'generate_course_plan', methods: ['POST'])]
    public function generateCoursePlan(
        Request $request,
        AIService $aiService,
        SyllabusRepository $syllabusRepository,
        EntityManagerInterface $em
    ): Response {
        // Permis à tous pour le moment
        // $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        // Accepter soit syllabus_id soit syllabus_text
        $syllabus = null;
        $syllabusText = null;

        if (isset($data['syllabus_id'])) {
            $syllabus = $syllabusRepository->findOneBy(['id' => $data['syllabus_id']]);
            if (!$syllabus) {
                return $this->json(['error' => 'Syllabus non trouvé'], 404);
            }
            $syllabusText = $syllabus->getRawText();
        } elseif (isset($data['syllabus_text'])) {
            $syllabusText = $data['syllabus_text'];
            // Créer un Syllabus temporaire
            $syllabus = new Syllabus();
            $syllabus->setFilename('syllabus_' . time() . '.txt');
            $syllabus->setRawText($syllabusText);
            $em->persist($syllabus);
        } else {
            return $this->json(['error' => 'syllabus_id ou syllabus_text requis'], 400);
        }

        if (!$syllabusText) {
            return $this->json(['error' => 'Le texte du syllabus n\'a pas pu être extrait'], 400);
        }

        try {
            // Paramètres optionnels
            $numberSessions = $data['number_sessions'] ?? $data['nombre_seances'] ?? 8;
            $durationPerSession = $data['duration_per_session'] ?? $data['duree_par_seance'] ?? 60;
            $studentLevel = $data['student_level'] ?? $data['niveau_etudiants'] ?? 'intermédiaire';
            $competences = $data['competences'] ?? [];

            // Appeler le service IA
            $coursePlanData = $aiService->generateCoursePlan(
                $syllabusText,
                $numberSessions,
                $durationPerSession,
                $studentLevel,
                $competences
            );

            // DEBUG: log la réponse IA
            error_log('AI Response Data: ' . json_encode($coursePlanData, JSON_PRETTY_PRINT));
            error_log('Seances count: ' . count($coursePlanData['seances'] ?? []));
            if (isset($coursePlanData['seances']) && count($coursePlanData['seances']) > 0) {
                error_log('First seance: ' . json_encode($coursePlanData['seances'][0], JSON_PRETTY_PRINT));
            }

            // Créer le CoursePlan
            $coursePlan = new CoursePlan();
            $coursePlan->setSyllabus($syllabus);
            $coursePlan->setPlanGeneral($coursePlanData['plan_general'] ?? '');
            
            // Doctrine's JSON type handles encoding automatically - pass array directly
            $evaluation = $coursePlanData['evaluation'] ?? [];
            if (is_string($evaluation)) {
                $evaluation = json_decode($evaluation, true) ?? [];
            }
            $coursePlan->setEvaluation($evaluation);

            // Créer les sessions
            if (isset($coursePlanData['seances']) && is_array($coursePlanData['seances'])) {
                foreach ($coursePlanData['seances'] as $seanceData) {
                    $session = new Session();
                    $session->setTitre($seanceData['titre'] ?? 'Séance');
                    
                    // Doctrine's JSON type handles encoding automatically - pass arrays directly
                    $objectifs = $seanceData['objectifs'] ?? [];
                    if (is_string($objectifs)) {
                        $objectifs = json_decode($objectifs, true) ?? [];
                    }
                    $session->setObjectifs($objectifs);
                    
                    $contenus = $seanceData['contenus'] ?? [];
                    if (is_string($contenus)) {
                        $contenus = json_decode($contenus, true) ?? [];
                    }
                    $session->setContenus($contenus);
                    
                    $activites = $seanceData['activites'] ?? [];
                    if (is_string($activites)) {
                        $activites = json_decode($activites, true) ?? [];
                    }
                    $session->setActivites($activites);
                    
                    $session->setDone(false);

                    $coursePlan->addSession($session);
                }
            }

            // Persister
            $em->persist($coursePlan);
            $em->flush();

            return $this->json([
                'message' => 'Plan de cours généré avec succès',
                'course_plan_id' => $coursePlan->getId(),
                'syllabus_id' => $syllabus->getId(),
                'sessions_count' => count($coursePlan->getSessions()),
                'plan_general' => $coursePlan->getPlanGeneral()
            ], 201);

        } catch (\Exception $e) {
            // Debug: log l'erreur complète
            error_log('AIController generateCoursePlan error: ' . $e->getMessage());
            error_log('AIController generateCoursePlan trace: ' . $e->getTraceAsString());
            
            return $this->json([
                'error' => 'Erreur lors de la génération du plan',
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    #[Route('/generate-exercises', name: 'generate_exercises', methods: ['POST'])]
    public function generateExercises(
        Request $request,
        AIService $aiService,
        EntityManagerInterface $em
    ): Response {
        // Permis à tous pour le moment
        // $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['session_id'])) {
            return $this->json(['error' => 'session_id requis'], 400);
        }

        try {
            $sessionId = $data['session_id'];
            $difficulty = $data['difficulte'] ?? 'moyen';
            $competences = $data['competences_ciblees'] ?? [];

            // Récupérer la session (via repository)
            $session = $em->getRepository(Session::class)->find($sessionId);
            if (!$session) {
                return $this->json(['error' => 'Session non trouvée'], 404);
            }

            // Construire le contenu de la session
            // Doctrine automatically deserializes JSON fields to arrays
            $contenus = $session->getContenus() ?? [];
            $sessionContent = implode("\n", $contenus);
            
            $objectifs = $session->getObjectifs() ?? [];
            $sessionContext = $session->getTitre() . ": " . implode(" | ", $objectifs);

            // Appeler l'IA
            $exercises = $aiService->generateExercises(
                $sessionContent,
                $difficulty,
                $competences,
                $sessionContext
            );

            return $this->json([
                'message' => 'Exercices générés avec succès',
                'session_id' => $sessionId,
                'exercises_count' => count($exercises),
                'exercises' => $exercises
            ], 201);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération des exercices',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/update-course-plan', name: 'update_course_plan', methods: ['POST'])]
    public function updateCoursePlan(
        Request $request,
        AIService $aiService,
        EntityManagerInterface $em
    ): Response {
        // Permis à tous pour le moment
        // $this->denyAccessUnlessGranted('ROLE_TEACHER');

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['course_plan_id'])) {
            return $this->json(['error' => 'course_plan_id requis'], 400);
        }

        try {
            $coursePlanId = $data['course_plan_id'];
            $coursePlan = $em->getRepository(CoursePlan::class)->find($coursePlanId);

            if (!$coursePlan) {
                return $this->json(['error' => 'Plan de cours non trouvé'], 404);
            }

            // Récupérer les données de progression
            $realProgress = [];
            $sessions = $coursePlan->getSessions();
            foreach ($sessions as $session) {
                $realProgress[] = [
                    'id' => $session->getId(),
                    'titre' => $session->getTitre(),
                    'done' => $session->isDone(),
                    'notes' => $session->getNotesReelles()
                ];
            }

            $studentFeedback = $data['feedback_etudiants'] ?? '';

            // Appeler l'IA
            $updatedPlan = $aiService->updateCoursePlan(
                $coursePlan->getPlanGeneral(),
                $realProgress,
                $studentFeedback
            );

            // Appliquer les modifications (optionnel - juste stocker la réponse pour le moment)
            return $this->json([
                'message' => 'Plan réajusté avec succès',
                'course_plan_id' => $coursePlanId,
                'updated_plan' => $updatedPlan
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du réajustement du plan',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
