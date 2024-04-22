<?php

namespace App\Repository;

use App\Entity\Nimbus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nimbus>
 *
 * @method Nimbus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nimbus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nimbus[]    findAll()
 * @method Nimbus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NimbusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nimbus::class);
    }
}
