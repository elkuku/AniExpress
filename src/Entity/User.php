<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="system_user")
 * @UniqueEntity(fields="email", message="This email address is already in use")
 */
class User implements UserInterface, Serializable
{
    public const ROLES
        = [
            'User'  => 'ROLE_USER',
            'Admin' => 'ROLE_ADMIN',
        ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $role = 'ROLE_USER';

    public function eraseCredentials(): void
    {
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array
    {
        return [$this->getRole()];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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
                $this->email,
            ]
        );
    }

    public function unserialize($serialized): void
    {
        list (
            $this->id,
            $this->email,
            )
            = unserialize($serialized);
    }
}
