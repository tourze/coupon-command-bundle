<?php

namespace Tourze\CouponCommandBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<CommandConfig>
 */
#[AsRepository(entityClass: CommandConfig::class)]
class CommandConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandConfig::class);
    }

    /**
     * 根据口令查找配置
     */
    public function findByCommand(string $command): ?CommandConfig
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.command = :command')
            ->setParameter('command', $command)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof CommandConfig ? $result : null;
    }

    /**
     * 根据优惠券ID查找口令配置
     *
     * 注意：由于 coupon 是 inverse side 关联，我们需要通过 owning side 来查询
     * 这里我们通过 Coupon 实体的 commandConfig 字段来查找
     */
    public function findByCouponId(string $couponId): ?CommandConfig
    {
        $result = $this->createQueryBuilder('c')
            ->innerJoin('Tourze\CouponCoreBundle\Entity\Coupon', 'coupon', 'WITH', 'coupon.commandConfig = c')
            ->andWhere('coupon.id = :couponId')
            ->setParameter('couponId', $couponId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof CommandConfig ? $result : null;
    }

    /**
     * 查找所有有限制的口令配置
     *
     * @return array<int, CommandConfig>
     */
    public function findAllWithLimits(): array
    {
        $result = $this->createQueryBuilder('c')
            ->innerJoin('c.commandLimit', 'cl')
            ->getQuery()
            ->getResult()
        ;

        return is_array($result) ? array_values(array_filter($result, fn ($item) => $item instanceof CommandConfig)) : [];
    }

    /**
     * 查找所有启用限制的口令配置
     *
     * @return array<int, CommandConfig>
     */
    public function findAllWithEnabledLimits(): array
    {
        $result = $this->createQueryBuilder('c')
            ->innerJoin('c.commandLimit', 'cl')
            ->andWhere('cl.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult()
        ;

        return is_array($result) ? array_values(array_filter($result, fn ($item) => $item instanceof CommandConfig)) : [];
    }

    /**
     * 检查口令是否重复
     */
    public function isCommandExists(string $command, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.command = :command')
            ->setParameter('command', $command)
        ;

        if (null !== $excludeId) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId)
            ;
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * 获取使用统计
     *
     * @return array<string, int>
     */
    public function getUsageStats(string $commandConfigId): array
    {
        /** @var array<string, string|int> $result */
        $result = $this->createQueryBuilder('c')
            ->select('count(ur.id) as totalUsage, sum(CASE WHEN ur.isSuccess = true THEN 1 ELSE 0 END) as successUsage')
            ->leftJoin('c.usageRecords', 'ur')
            ->andWhere('c.id = :commandConfigId')
            ->setParameter('commandConfigId', $commandConfigId)
            ->getQuery()
            ->getSingleResult()
        ;

        $totalUsage = (int) ($result['totalUsage'] ?? 0);
        $successUsage = (int) ($result['successUsage'] ?? 0);

        return [
            'totalUsage' => $totalUsage,
            'successUsage' => $successUsage,
            'failureUsage' => $totalUsage - $successUsage,
        ];
    }

    public function save(CommandConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommandConfig $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
