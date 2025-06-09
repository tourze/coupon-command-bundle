<?php

namespace Tourze\CouponCommandBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;

/**
 * @method CommandUsageRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandUsageRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandUsageRecord[]    findAll()
 * @method CommandUsageRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandUsageRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandUsageRecord::class);
    }

    /**
     * 根据用户ID查找使用记录
     */
    public function findByUserId(string $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据口令配置ID查找使用记录
     */
    public function findByCommandConfigId(string $commandConfigId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->setParameter('commandConfigId', $commandConfigId)
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计用户使用某个口令的次数
     */
    public function countByUserAndCommandConfig(string $userId, string $commandConfigId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->setParameter('userId', $userId)
            ->setParameter('commandConfigId', $commandConfigId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 统计用户成功使用某个口令的次数
     */
    public function countSuccessByUserAndCommandConfig(string $userId, string $commandConfigId): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->andWhere('c.isSuccess = :isSuccess')
            ->setParameter('userId', $userId)
            ->setParameter('commandConfigId', $commandConfigId)
            ->setParameter('isSuccess', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
