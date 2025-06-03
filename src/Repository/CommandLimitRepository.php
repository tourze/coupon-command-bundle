<?php

namespace Tourze\CouponCommandBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCommandBundle\Entity\CommandLimit;

/**
 * @method CommandLimit|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandLimit|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandLimit[]    findAll()
 * @method CommandLimit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandLimitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandLimit::class);
    }

    /**
     * 根据口令配置ID查找限制配置
     */
    public function findByCommandConfigId(string $commandConfigId): ?CommandLimit
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->setParameter('commandConfigId', $commandConfigId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找所有启用的限制配置
     */
    public function findAllEnabled(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true)
            ->getQuery()
            ->getResult();
    }
} 