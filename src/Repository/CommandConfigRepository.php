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

    /**
     * 根据口令查找配置
     */
    public function findByCommand(string $command): ?CommandConfig
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.command = :command')
            ->setParameter('command', $command)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 根据优惠券ID查找口令配置
     */
    public function findByCouponId(string $couponId): ?CommandConfig
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.coupon = :couponId')
            ->setParameter('couponId', $couponId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找所有有限制的口令配置
     */
    public function findAllWithLimits(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.commandLimit', 'cl')
            ->andWhere('cl.id IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找所有启用限制的口令配置
     */
    public function findAllWithEnabledLimits(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.commandLimit', 'cl')
            ->andWhere('cl.id IS NOT NULL')
            ->andWhere('cl.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * 检查口令是否重复
     */
    public function isCommandExists(string $command, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.command = :command')
            ->setParameter('command', $command);

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * 获取使用统计
     */
    public function getUsageStats(string $commandConfigId): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('count(ur.id) as totalUsage, count(CASE WHEN ur.isSuccess = true THEN 1 END) as successUsage')
            ->leftJoin('c.usageRecords', 'ur')
            ->andWhere('c.id = :commandConfigId')
            ->setParameter('commandConfigId', $commandConfigId)
            ->getQuery()
            ->getSingleResult();

        return [
            'totalUsage' => (int) $result['totalUsage'],
            'successUsage' => (int) $result['successUsage'],
            'failureUsage' => (int) $result['totalUsage'] - (int) $result['successUsage'],
        ];
    }
}
