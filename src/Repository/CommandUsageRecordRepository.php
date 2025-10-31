<?php

namespace Tourze\CouponCommandBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<CommandUsageRecord>
 */
#[AsRepository(entityClass: CommandUsageRecord::class)]
class CommandUsageRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandUsageRecord::class);
    }

    /**
     * 根据用户ID查找使用记录
     *
     * @return array<int, CommandUsageRecord>
     */
    public function findByUserId(string $userId): array
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return is_array($result) ? array_values(array_filter($result, fn ($item) => $item instanceof CommandUsageRecord)) : [];
    }

    /**
     * 根据口令配置ID查找使用记录
     *
     * @return array<int, CommandUsageRecord>
     */
    public function findByCommandConfigId(string $commandConfigId): array
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->setParameter('commandConfigId', $commandConfigId)
            ->orderBy('c.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return is_array($result) ? array_values(array_filter($result, fn ($item) => $item instanceof CommandUsageRecord)) : [];
    }

    /**
     * 统计用户使用某个口令的次数
     */
    public function countByUserAndCommandConfig(string $userId, string $commandConfigId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->setParameter('userId', $userId)
            ->setParameter('commandConfigId', $commandConfigId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 统计用户成功使用某个口令的次数
     */
    public function countSuccessByUserAndCommandConfig(string $userId, string $commandConfigId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.commandConfig = :commandConfigId')
            ->andWhere('c.isSuccess = :isSuccess')
            ->setParameter('userId', $userId)
            ->setParameter('commandConfigId', $commandConfigId)
            ->setParameter('isSuccess', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function save(CommandUsageRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommandUsageRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
