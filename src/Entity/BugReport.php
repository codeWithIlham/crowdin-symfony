<?php

namespace App\Entity;

use App\Repository\BugReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BugReportRepository::class)]
class BugReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $reporter = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $page = null;

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column]
    private bool $isResolved = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getReporter(): ?User { return $this->reporter; }
    public function setReporter(?User $reporter): static { $this->reporter = $reporter; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getPage(): ?string { return $this->page; }
    public function setPage(?string $page): static { $this->page = $page; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function isResolved(): bool { return $this->isResolved; }
    public function setIsResolved(bool $isResolved): static { $this->isResolved = $isResolved; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
