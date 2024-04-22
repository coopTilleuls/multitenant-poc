<?php

namespace App\Entity;
use App\Repository\NimbusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: 'nimbus')]
#[ORM\Entity(repositoryClass: NimbusRepository::class)]
class Nimbus
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    public ?string $nom;

    #[ORM\Column(options: ['default' => false])]
    public bool $ia = false;

    #[ORM\ManyToOne(inversedBy: 'nimbuses')]
    public ?User $owner = null;

    public function getId(): Uuid
    {
        return $this->id;
    }
}
