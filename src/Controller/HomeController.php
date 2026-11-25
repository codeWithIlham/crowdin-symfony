<?php

namespace App\Controller;

use App\Entity\BugReport;
use App\Repository\LanguageRepository;
use App\Repository\ProjectRepository;
use App\Repository\TranslationRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(
        ProjectRepository $projects,
        TranslationRepository $translations,
        UserRepository $users,
        LanguageRepository $languages,
    ): Response {
        $totalTranslations = count($translations->findAll());
        $approved = count($translations->findBy(['isApproved' => true]));

        return $this->render('home/about.html.twig', [
            'totalProjects'     => count($projects->findAll()),
            'totalTranslations' => $totalTranslations,
            'approvedCount'     => $approved,
            'totalUsers'        => count($users->findAll()),
            'totalLanguages'    => count($languages->findAll()),
            'approvalRate'      => $totalTranslations > 0 ? round($approved / $totalTranslations * 100) : 0,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, NotificationService $notif, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $type = $request->request->get('form_type');

            if ($type === 'bug') {
                $title = $request->request->get('bug_title', '');
                $desc  = $request->request->get('bug_desc', '');
                $page  = $request->request->get('bug_page', '');

                $bug = new BugReport();
                $bug->setReporter($this->getUser());
                $bug->setTitle($title);
                $bug->setPage($page ?: null);
                $bug->setDescription($desc);
                $em->persist($bug);
                $em->flush();

                $who = $this->getUser()?->getEmail() ?? 'Inconnu';
                $notif->notifyAllAdmins(
                    "🐛 Bug signalé par $who : « $title »",
                    '/admin/bugs'
                );
                $em->flush();
                $this->addFlash('success_bug', 'Bug signalé, merci ! L\'admin a été notifié.');
            }

            return $this->redirectToRoute('app_contact', ['tab' => 'bug']);
        }

        $changelog = [
            ['date' => '30 juin 2026',   'version' => 'v1.3', 'label' => 'feature',  'text' => 'Système de notifications temps réel entre admin et traducteurs'],
            ['date' => '30 juin 2026',   'version' => 'v1.2', 'label' => 'feature',  'text' => 'Nouveau design neumorphic sur toute la plateforme'],
            ['date' => '30 juin 2026',   'version' => 'v1.1', 'label' => 'feature',  'text' => 'Tableau de révision Kanban et éditeur focus traducteur'],
            ['date' => '30 juin 2026',   'version' => 'v1.0', 'label' => 'feature',  'text' => 'Import de contenu (texte libre ou fichier), workflow admin/traducteur'],
            ['date' => '28 juin 2026',   'version' => 'v0.3', 'label' => 'fix',      'text' => 'Correction validation email côté serveur à l\'inscription'],
            ['date' => '18 juin 2026',   'version' => 'v0.2', 'label' => 'feature',  'text' => 'Authentification Symfony, rôles admin et traducteur'],
            ['date' => '18 juin 2026',   'version' => 'v0.1', 'label' => 'initial',  'text' => 'Mise en place du projet Symfony 6.4 avec Docker'],
        ];

        $activeTab = $request->query->get('tab', 'changelog');

        if ($activeTab === 'bug' && $this->isGranted('ROLE_ADMIN')) {
            $activeTab = 'changelog';
        }

        return $this->render('home/contact.html.twig', [
            'changelog'  => $changelog,
            'active_tab' => $activeTab,
        ]);
    }
}
