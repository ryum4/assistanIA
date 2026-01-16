<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'], $data['name'])) {
            return $this->json(['error' => 'Email, password et name sont requis'], 400);
        }

        $email = $data['email'];
        $password = $data['password'];
        $name = $data['name'];

        // Vérifier si l'utilisateur existe déjà
        if ($userRepository->findByEmail($email)) {
            return $this->json(['error' => 'Un utilisateur avec cet email existe déjà'], 409);
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setPassword(
            $userPasswordHasher->hashPassword($user, $password)
        );

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName()
            ]
        ], 201);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Email et password requis'], 400);
        }

        $user = $userRepository->findByEmail($data['email']);

        if (!$user || !$userPasswordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        // Store user info in session
        $session = $request->getSession();
        $session->set('user_email', $user->getEmail());
        $session->set('user_id', $user->getId());
        $session->set('user_name', $user->getName());
        $session->set('user_roles', $user->getRoles());
        $session->set('authenticated', true);

        return $this->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ], 200);
    }

    #[Route('/me', name: 'app_me', methods: ['GET'])]
    public function me(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles()
            ]
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): Response
    {
        // Symfony gère automatiquement le logout via le firewall
        return $this->json(['message' => 'Déconnecté']);
    }
}
