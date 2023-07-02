<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaskRepository;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut excéder {{ limit }} caractères.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu ne peut être vide.')]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: false)]
    private ?bool $isDone = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->isDone = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function isIsDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): static
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
    
    public function toggle($flag)
    {
        $this->isDone = $flag;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
