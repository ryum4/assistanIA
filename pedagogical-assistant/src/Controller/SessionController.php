<?php

namespace App\Controller;

use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sessions')]
class SessionController extends AbstractController
{
    #[Route('/{id}/complete', name: 'session_complete', methods: ['PATCH'])]
    public function completeSession(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Pas de vérification d'authentification pour MVP (demo)

        $session = $em->getRepository(Session::class)->find($id);
        if (!$session) {
            return $this->json(['error' => 'Session non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);

        try {
            // Marquer la séance comme faite
            $session->setDone(true);

            // Ajouter les notes si fournies
            if (isset($data['notes_reelles'])) {
                $session->setNotesReelles($data['notes_reelles']);
            }

            $em->persist($session);
            $em->flush();

            return $this->json([
                'message' => 'Séance marquée comme terminée',
                'session_id' => $id,
                'session' => [
                    'titre' => $session->getTitre(),
                    'done' => $session->isDone(),
                    'notes' => $session->getNotesReelles()
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}', name: 'session_show', methods: ['GET'])]
    public function getSession(int $id, EntityManagerInterface $em): Response
    {
        $session = $em->getRepository(Session::class)->find($id);
        if (!$session) {
            return $this->json(['error' => 'Session non trouvée'], 404);
        }

        return $this->json([
            'id' => $session->getId(),
            'titre' => $session->getTitre(),
            'objectifs' => $session->getObjectifs(),
            'contenus' => $session->getContenus(),
            'activites' => $session->getActivites(),
            'done' => $session->isDone(),
            'notes_reelles' => $session->getNotesReelles(),
            'course_plan_id' => $session->getCoursePlan()->getId()
        ]);
    }
}
