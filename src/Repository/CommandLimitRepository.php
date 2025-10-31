<?php

namespace Tourze\CouponCommandBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<CommandLimit>
 */
#[AsRepository(entityClass: CommandLimit::class)]
class CommandLimitRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly CommandConfigRepository $commandConfigRepository,
    ) {
        parent::__construct($registry, CommandLimit::class);
    }

    /**
     * 根据口令配置ID查找限制配置
     *
     * 由于 CommandLimit.commandConfig 关联被临时注释掉，
     * 我们通过 CommandConfig 的 owning side 关联来查找
     */
    public function findByCommandConfigId(string $commandConfigId): ?CommandLimit
    {
        // 首先查找CommandConfig以获取command_limit_id
        $commandConfig = $this->commandConfigRepository->find($commandConfigId);

        if (null === $commandConfig) {
            return null;
        }

        // 通过CommandConfig的commandLimit关联来获取CommandLimit
        return $commandConfig->getCommandLimit();
    }

    /**
     * 查找所有启用的限制配置
     *
     * @return array<int, CommandLimit>
     */
    public function findAllEnabled(): array
    {
        $result = $this->createQueryBuilder('c')
            ->andWhere('c.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true)
            ->getQuery()
            ->getResult()
        ;

        return is_array($result) ? array_values(array_filter($result, fn ($item) => $item instanceof CommandLimit)) : [];
    }

    public function save(CommandLimit $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommandLimit $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
