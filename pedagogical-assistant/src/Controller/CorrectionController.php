<?php

namespace App\Controller;

use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class CorrectionController extends AbstractController
{
    #[Route('/correct-exercise', name: 'correct_exercise', methods: ['POST'])]
    public function correctExercise(Request $request, AIService $aiService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_TEACHER');
        
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['question'], $data['answer'])) {
            return $this->json(['error' => 'Question et answer requis'], 400);
        }

        try {
            $correction = $aiService->correctExercise(
                $data['question'],
                $data['answer'],
                $data['expected_answer'] ?? ''
            );
            
            return $this->json([
                'correction' => $correction,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
