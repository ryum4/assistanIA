<?php

namespace App\Controller;

use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class ChatController extends AbstractController
{
    #[Route('/chat', name: 'ai_chat', methods: ['POST'])]
    public function chat(Request $request, AIService $aiService): Response
    {
        // Permis à tous pour le moment (peut être limité au besoin)
        // $this->denyAccessUnlessGranted('ROLE_TEACHER');
        
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['message'])) {
            return $this->json(['error' => 'Message requis'], 400);
        }

        $message = $data['message'];
        $context = $data['context'] ?? null; // Contexte optionnel (topic, level, etc)

        try {
            $response = $aiService->chat($message, $context);
            
            return $this->json([
                'message' => $message,
                'response' => $response,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur IA: ' . $e->getMessage()
            ], 500);
        }
    }
}
