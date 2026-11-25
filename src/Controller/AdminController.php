<?php

namespace App\Controller;

use App\Repository\BugReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/bugs', name: 'app_admin_bugs')]
    public function bugs(BugReportRepository $repo): Response
    {
        return $this->render('admin/bugs.html.twig', [
            'open'     => $repo->findBy(['isResolved' => false], ['createdAt' => 'DESC']),
            'resolved' => $repo->findBy(['isResolved' => true],  ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/bugs/{id}/resolve', name: 'app_admin_bug_resolve', methods: ['POST'])]
    public function resolveBug(int $id, BugReportRepository $repo, EntityManagerInterface $em): Response
    {
        $bug = $repo->find($id);
        if ($bug) {
            $bug->setIsResolved(true);
            $em->flush();
            $this->addFlash('success', 'Bug marqué comme résolu.');
        }
        return $this->redirectToRoute('app_admin_bugs');
    }
}
