<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du projet est requis.')]
    #[Assert\Length(max: 255)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Language $sourceLanguage = null;

    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_language')]
    private Collection $targetLanguages;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Translation::class, orphanRemoval: true)]
    private Collection $translations;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'project_user')]
    private Collection $translators;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->targetLanguages = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->translators = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $owner): static { $this->owner = $owner; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getSourceLanguage(): ?Language { return $this->sourceLanguage; }
    public function setSourceLanguage(?Language $sourceLanguage): static { $this->sourceLanguage = $sourceLanguage; return $this; }

    public function getTargetLanguages(): Collection { return $this->targetLanguages; }

    public function addTargetLanguage(Language $language): static
    {
        if (!$this->targetLanguages->contains($language)) {
            $this->targetLanguages->add($language);
        }
        return $this;
    }

    public function removeTargetLanguage(Language $language): static
    {
        $this->targetLanguages->removeElement($language);
        return $this;
    }

    public function getTranslators(): Collection { return $this->translators; }

    public function addTranslator(User $user): static
    {
        if (!$this->translators->contains($user)) {
            $this->translators->add($user);
        }
        return $this;
    }

    public function removeTranslator(User $user): static
    {
        $this->translators->removeElement($user);
        return $this;
    }

    public function hasTranslator(User $user): bool
    {
        return $this->translators->contains($user);
    }

    public function getTranslations(): Collection { return $this->translations; }

    public function addTranslation(Translation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setProject($this);
        }
        return $this;
    }

    public function removeTranslation(Translation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            if ($translation->getProject() === $this) {
                $translation->setProject(null);
            }
        }
        return $this;
    }
}
