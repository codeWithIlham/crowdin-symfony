<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class DemoController extends AbstractController
{
    #[Route('/demo/{role}', name: 'app_demo', requirements: ['role' => 'admin|manager|translator|both'])]
    public function demo(
        string $role,
        Security $security,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $email    = match($role) {
            'admin'      => 'admin@demo.crowdin',
            'manager'    => 'manager@demo.crowdin',
            'both'       => 'both@demo.crowdin',
            default      => 'translator@demo.crowdin',
        };
        $username = match($role) {
            'admin'      => 'Demo Administrateur',
            'manager'    => 'Demo Chef de projet',
            'both'       => 'Demo Les Deux',
            default      => 'Demo Traducteur',
        };
        $roles    = match($role) {
            'admin'      => ['ROLE_ADMIN'],
            'manager'    => ['ROLE_PROJECT_MANAGER'],
            'both'       => ['ROLE_PROJECT_MANAGER'],
            default      => ['ROLE_USER'],
        };

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($hasher->hashPassword($user, 'demo1234'));
            $em->persist($user);
        }

        $user->setRoles($roles);
        $em->flush();

        $response = $security->login($user, 'security.authenticator.form_login.main', 'main');

        return $response ?? $this->redirectToRoute('app_dashboard');
    }
}
