<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserLanguage
{
    const LEVEL_BEGINNER     = 'beginner';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED     = 'advanced';
    const LEVEL_NATIVE       = 'native';

    const LEVELS = [
        'Débutant'      => self::LEVEL_BEGINNER,
        'Intermédiaire' => self::LEVEL_INTERMEDIATE,
        'Avancé'        => self::LEVEL_ADVANCED,
        'Natif'         => self::LEVEL_NATIVE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Language $language = null;

    #[ORM\Column(length: 20)]
    private string $level = self::LEVEL_BEGINNER;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): static { $this->language = $language; return $this; }

    public function getLevel(): string { return $this->level; }
    public function setLevel(string $level): static { $this->level = $level; return $this; }

    public function getLevelLabel(): string
    {
        return array_search($this->level, self::LEVELS) ?: ucfirst($this->level);
    }
}
