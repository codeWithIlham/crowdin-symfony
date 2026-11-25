<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    public function index(NotificationRepository $repo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $notifications = $repo->findForUser($user);
        $repo->markAllRead($user);
        $em->flush();

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/api/notifications/count', name: 'app_notifications_count')]
    public function count(NotificationRepository $repo): JsonResponse
    {
        return new JsonResponse(['count' => $repo->countUnread($this->getUser())]);
    }

    #[Route('/notifications/{id}/delete', name: 'app_notification_delete', methods: ['POST'])]
    public function delete(Notification $notification, EntityManagerInterface $em): Response
    {
        if ($notification->getRecipient() === $this->getUser()) {
            $em->remove($notification);
            $em->flush();
        }

        return $this->redirectToRoute('app_notifications');
    }
}
