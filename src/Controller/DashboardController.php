<?php

namespace App\Controller;

use App\Repository\BugReportRepository;
use App\Repository\ProjectRepository;
use App\Repository\TranslationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProjectRepository $projectRepository,
        TranslationRepository $translationRepository,
        BugReportRepository $bugReports,
    ): Response {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $allProjects = $projectRepository->findAll();
            $allTranslations = $translationRepository->findAll();
            $doneTranslations = array_filter($allTranslations, fn($t) => $t->getTranslatedText() !== null && $t->getTranslatedText() !== '');

            return $this->render('dashboard/index.html.twig', [
                'isAdmin'              => true,
                'isProjectManager'     => false,
                'allProjects'          => $allProjects,
                'totalProjects'        => count($allProjects),
                'totalTranslations'    => count($allTranslations),
                'doneTranslations'     => count($doneTranslations),
                'pendingTranslations'  => count($allTranslations) - count($doneTranslations),
                'openBugs'             => count($bugReports->findBy(['isResolved' => false])),
            ]);
        }

        if ($this->isGranted('ROLE_PROJECT_MANAGER')) {
            $myProjects = $projectRepository->findByOwner($user);
            $totalTranslations = 0;
            $doneTranslations  = 0;
            foreach ($myProjects as $project) {
                foreach ($project->getTranslations() as $t) {
                    $totalTranslations++;
                    if ($t->getTranslatedText() !== null && $t->getTranslatedText() !== '') {
                        $doneTranslations++;
                    }
                }
            }

            return $this->render('dashboard/index.html.twig', [
                'isAdmin'             => false,
                'isProjectManager'    => true,
                'allProjects'         => $myProjects,
                'totalProjects'       => count($myProjects),
                'totalTranslations'   => $totalTranslations,
                'doneTranslations'    => $doneTranslations,
                'pendingTranslations' => $totalTranslations - $doneTranslations,
            ]);
        }

        $allProjects = $projectRepository->findVisibleForTranslator($user);
        $allTranslations = $translationRepository->findAll();
        $myDone = $translationRepository->countDoneByTranslator($user);

        return $this->render('dashboard/index.html.twig', [
            'isAdmin'          => false,
            'isProjectManager' => false,
            'allProjects'      => $allProjects,
            'myDone'           => $myDone,
            'totalStrings'     => count($allTranslations),
        ]);
    }
}
