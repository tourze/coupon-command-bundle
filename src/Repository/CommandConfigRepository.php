<?php

namespace Tourze\CouponCommandBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCommandBundle\Entity\CommandConfig;


/**
 * @method CommandConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandConfig[]    findAll()
 * @method CommandConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandConfigRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandConfig::class);
    }
}
