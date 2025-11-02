<?php

namespace App\Infrastructure\Persistence\Doctrine\Order;

use App\Domain\Entity\Order\OrderEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderEntity>
 */
class OrderEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderEntity::class);
    }

    public function store(OrderEntity $newOrder): void
    {
        $this->getEntityManager()->persist($newOrder);
        $this->getEntityManager()->flush();
    }
}
