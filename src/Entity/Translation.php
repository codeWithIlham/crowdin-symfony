<?php

namespace App\Entity;

use App\Repository\TranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class Translation
{
    const STATUS_PENDING    = 'pending';
    const STATUS_SUBMITTED  = 'submitted';
    const STATUS_APPROVED   = 'approved';
    const STATUS_REJECTED   = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $sourceKey = null;

    #[ORM\Column(type: 'text')]
    private ?string $sourceText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $translatedText = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $translator = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reviewComment = null;

    #[ORM\Column]
    private bool $isApproved = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $project): static { $this->project = $project; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): static { $this->language = $language; return $this; }

    public function getSourceKey(): ?string { return $this->sourceKey; }
    public function setSourceKey(string $sourceKey): static { $this->sourceKey = $sourceKey; return $this; }

    public function getSourceText(): ?string { return $this->sourceText; }
    public function setSourceText(string $sourceText): static { $this->sourceText = $sourceText; return $this; }

    public function getTranslatedText(): ?string { return $this->translatedText; }
    public function setTranslatedText(?string $translatedText): static { $this->translatedText = $translatedText; return $this; }

    public function getTranslator(): ?User { return $this->translator; }
    public function setTranslator(?User $translator): static { $this->translator = $translator; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getReviewComment(): ?string { return $this->reviewComment; }
    public function setReviewComment(?string $reviewComment): static { $this->reviewComment = $reviewComment; return $this; }

    public function isApproved(): bool { return $this->isApproved; }
    public function setIsApproved(bool $isApproved): static { $this->isApproved = $isApproved; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}
