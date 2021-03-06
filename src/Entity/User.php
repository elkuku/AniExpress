<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'system_user')]
#[UniqueEntity(fields: 'identifier', message: 'This identifier is already in use')]
class User implements UserInterface, Serializable
{
    public const ROLES
        = [
            'user'  => 'ROLE_USER',
            'admin' => 'ROLE_ADMIN',
        ];

    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = 0;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $identifier = '';

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $role = 'ROLE_USER';

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $googleId = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $gitHubId = '';

    public function eraseCredentials(): void
    {
    }

    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function setUserIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->identifier;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): void
    {
    }

    public function serialize(): string
    {
        return serialize(
            [
                $this->id,
                $this->identifier,
            ]
        );
    }

    public function unserialize($data): void
    {
        [
            $this->id,
            $this->identifier,
        ]
            = unserialize($data, ['allowed_classes' => [__CLASS__]]);
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getGitHubId(): ?string
    {
        return $this->gitHubId;
    }

    public function setGitHubId(?string $gitHubId): self
    {
        $this->gitHubId = $gitHubId;

        return $this;
    }
}
