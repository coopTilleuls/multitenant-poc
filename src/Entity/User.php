<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\ManyToOne]
    private ?User $owner = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $sql_db_name;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $sql_user_name;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $db_created = false;

    #[ORM\OneToMany(targetEntity: Books::class, mappedBy: 'owner')]
    private Collection $nimbuses;

    public function __construct()
    {
        $this->nimbuses = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getSqlDbName(): string
    {
        return $this->sql_db_name;
    }

    public function setSqlDbName(string $sql_db_name): void
    {
        $this->sql_db_name = $sql_db_name;
    }

    public function getSqlUserName(): string
    {
        return $this->sql_user_name;
    }

    public function setSqlUserName(string $sql_user_name): void
    {
        $this->sql_user_name = $sql_user_name;
    }

    public function isDbCreated(): bool
    {
        return $this->db_created;
    }

    public function setDbCreated(bool $db_created): void
    {
        $this->db_created = $db_created;
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }

    /**
     * @return Collection<int, Books>
     */
    public function getNimbuses(): Collection
    {
        return $this->nimbuses;
    }

    public function addNimbus(Books $nimbus): static
    {
        if (!$this->nimbuses->contains($nimbus)) {
            $this->nimbuses->add($nimbus);
            $nimbus->setOwner($this);
        }

        return $this;
    }

    public function removeNimbus(Books $nimbus): static
    {
        if ($this->nimbuses->removeElement($nimbus)) {
            // set the owning side to null (unless already changed)
            if ($nimbus->getOwner() === $this) {
                $nimbus->setOwner(null);
            }
        }

        return $this;
    }
}
