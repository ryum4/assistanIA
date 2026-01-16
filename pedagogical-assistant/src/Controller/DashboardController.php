<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard_home')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/login', name: 'dashboard_login')]
    public function login(): Response
    {
        return $this->render('dashboard/login.html.twig');
    }

    #[Route('/courses', name: 'dashboard_courses')]
    public function courses(Request $request): Response
    {
        // Start session if not already started
        $session = $request->getSession();
        if (!$session->has('authenticated')) {
            return $this->redirect('/login');
        }
        
        return $this->render('dashboard/courses.html.twig');
    }

    #[Route('/course/{id}', name: 'dashboard_course_detail', requirements: ['id' => '\d+'])]
    public function courseDetail(Request $request, int $id): Response
    {
        // Temporarily allow access to debug
        // $session = $request->getSession();
        // if (!$session->has('authenticated')) {
        //     return $this->redirect('/login');
        // }
        
        return $this->render('dashboard/course-detail.html.twig', ['courseId' => $id]);
    }

    #[Route('/chat', name: 'dashboard_chat')]
    public function chat(Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->has('authenticated')) {
            return $this->redirect('/login');
        }
        
        return $this->render('dashboard/chat.html.twig');
    }

    #[Route('/api-test', name: 'api_test')]
    public function apiTest(): Response
    {
        return new Response(file_get_contents(__DIR__ . '/../../templates/api-test.html.twig'), 200, [
            'Content-Type' => 'text/html'
        ]);
    }
}
