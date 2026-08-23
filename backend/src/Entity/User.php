<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarPath = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $consentedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCollection::class, cascade: ['remove'])]
    private Collection $userCollections;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Review::class, cascade: ['remove'])]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: MovieList::class, cascade: ['remove'])]
    private Collection $movieLists;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: ListComment::class, cascade: ['remove'])]
    private Collection $listComments;

    public function __construct()
    {
        $this->userCollections = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->movieLists = new ArrayCollection();
        $this->listComments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    #[\Deprecated]
    public function eraseCredentials(): void {}

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(string $username): static { $this->username = $username; return $this; }

    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): static { $this->bio = $bio; return $this; }

    public function getAvatarPath(): ?string { return $this->avatarPath; }
    public function setAvatarPath(?string $avatarPath): static { $this->avatarPath = $avatarPath; return $this; }

    public function getConsentedAt(): ?\DateTimeImmutable { return $this->consentedAt; }
    public function setConsentedAt(?\DateTimeImmutable $consentedAt): static { $this->consentedAt = $consentedAt; return $this; }

    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $resetToken): static { $this->resetToken = $resetToken; return $this; }

    public function getResetTokenExpiresAt(): ?\DateTimeImmutable { return $this->resetTokenExpiresAt; }
    public function setResetTokenExpiresAt(?\DateTimeImmutable $resetTokenExpiresAt): static { $this->resetTokenExpiresAt = $resetTokenExpiresAt; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getUserCollections(): Collection { return $this->userCollections; }
    public function getReviews(): Collection { return $this->reviews; }
    public function getMovieLists(): Collection { return $this->movieLists; }
    public function getListComments(): Collection { return $this->listComments; }
}
