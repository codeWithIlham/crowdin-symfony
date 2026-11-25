<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Project;
use App\Entity\Translation;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
    ) {}

    public function notifyAllTranslators(string $message, ?string $link = null): void
    {
        $translators = $this->userRepository->findTranslators();
        foreach ($translators as $user) {
            $this->create($user, $message, $link);
        }
    }

    public function notifyAllAdmins(string $message, ?string $link = null): void
    {
        $admins = $this->userRepository->findAdmins();
        foreach ($admins as $user) {
            $this->create($user, $message, $link);
        }
    }

    public function notify(User $recipient, string $message, ?string $link = null): void
    {
        $this->create($recipient, $message, $link);
    }

    private function create(User $recipient, string $message, ?string $link): void
    {
        $notif = new Notification();
        $notif->setRecipient($recipient);
        $notif->setMessage($message);
        $notif->setLink($link);
        $this->em->persist($notif);
    }
}
