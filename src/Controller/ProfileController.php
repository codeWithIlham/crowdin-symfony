<?php

namespace App\Controller;

use App\Entity\Language;
use App\Entity\UserLanguage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();
        $error = null;

        $action = $request->request->get('action', '');

        // Mise à jour du profil (description + username)
        if ($request->isMethod('POST') && $action === 'profile') {
            $username = trim($request->request->get('username', ''));
            if ($username !== '') {
                $user->setUsername($username);
            }
            $user->setDescription($request->request->get('description', '') ?: null);
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_profile');
        }

        // Ajouter une langue
        if ($request->isMethod('POST') && $action === 'add_language') {
            $langId   = (int) $request->request->get('language_id');
            $level    = $request->request->get('level', UserLanguage::LEVEL_BEGINNER);
            $language = $em->getRepository(Language::class)->find($langId);

            if ($language) {
                $existing = $em->getRepository(UserLanguage::class)->findOneBy(['user' => $user, 'language' => $language]);
                if ($existing) {
                    $this->addFlash('error', 'Cette langue est déjà dans votre profil.');
                } else {
                    $ul = new UserLanguage();
                    $ul->setUser($user);
                    $ul->setLanguage($language);
                    $ul->setLevel(in_array($level, array_values(UserLanguage::LEVELS)) ? $level : UserLanguage::LEVEL_BEGINNER);
                    $em->persist($ul);
                    $em->flush();
                    $this->addFlash('success', 'Langue ajoutée.');
                }
            }
            return $this->redirectToRoute('app_profile');
        }

        // Supprimer une langue
        if ($request->isMethod('POST') && $action === 'remove_language') {
            $ulId = (int) $request->request->get('user_language_id');
            $ul   = $em->getRepository(UserLanguage::class)->find($ulId);
            if ($ul && $ul->getUser() === $user) {
                $em->remove($ul);
                $em->flush();
                $this->addFlash('success', 'Langue supprimée.');
            }
            return $this->redirectToRoute('app_profile');
        }

        // Changer le mot de passe
        if ($request->isMethod('POST') && $action === 'password') {
            $current = $request->request->get('current_password', '');
            $new     = $request->request->get('new_password', '');
            $confirm = $request->request->get('confirm_password', '');

            if (!$hasher->isPasswordValid($user, $current)) {
                $error = 'Mot de passe actuel incorrect.';
            } elseif (strlen($new) < 6) {
                $error = 'Le nouveau mot de passe doit faire au moins 6 caractères.';
            } elseif ($new !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $user->setPassword($hasher->hashPassword($user, $new));
                $em->flush();
                $this->addFlash('success', 'Mot de passe mis à jour.');
                return $this->redirectToRoute('app_profile');
            }
        }

        $userLanguages = $em->getRepository(UserLanguage::class)->findBy(['user' => $user]);
        $allLanguages  = $em->getRepository(Language::class)->findBy([], ['name' => 'ASC']);

        return $this->render('profile/index.html.twig', [
            'error'         => $error,
            'userLanguages' => $userLanguages,
            'allLanguages'  => $allLanguages,
        ]);
    }
}
