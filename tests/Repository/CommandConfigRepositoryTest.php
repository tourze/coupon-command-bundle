<?php

namespace Tourze\CouponCommandBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * CommandConfig Repository 测试
 *
 * 此测试类继承自AbstractRepositoryTestCase，自动生成针对所有实体字段的测试。
 *
 * 注意：CommandConfig实体包含 inverse side 关联字段 'coupon' (mappedBy='commandConfig')，
 * 这种字段不能用于 Doctrine 的 findBy 查询排序。
 *
 * 由于父类的 testFindOneByShouldSortOrder 方法是 final 的无法重写，
 * 我们通过修改实体映射来解决这个问题。
 *
 * @internal
 */
#[CoversClass(CommandConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class CommandConfigRepositoryTest extends AbstractRepositoryTestCase
{
    private CommandConfigRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CommandConfigRepository::class);
    }

    public function testInheritance(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testRepositoryClassInitialization(): void
    {
        $this->assertInstanceOf(CommandConfigRepository::class, $this->repository);
    }

    public function testRepositoryCanCreateNewEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST123');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('TEST_COMMAND');
        $entity->setCoupon($coupon);

        $this->repository->save($entity, false);
        $this->assertNotNull($entity->getId());
    }

    public function testFindByCommandReturnsCorrectEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('FIND123');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('FIND_TEST');
        $this->setupBidirectionalAssociation($entity, $coupon);
        $this->repository->save($entity, true);

        $result = $this->repository->findByCommand('FIND_TEST');
        $this->assertNotNull($result);
        $this->assertEquals('FIND_TEST', $result->getCommand());

        $notFound = $this->repository->findByCommand('NOT_EXISTS');
        $this->assertNull($notFound);
    }

    public function testFindByCouponIdReturnsCorrectEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('COUPON123');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('COUPON_TEST');
        $this->setupBidirectionalAssociation($entity, $coupon);

        $this->repository->save($entity, true);

        $result = $this->repository->findByCouponId((string) $coupon->getId());
        $this->assertNotNull($result);
        $this->assertEquals($coupon->getId(), $result->getCoupon()?->getId());

        $notFound = $this->repository->findByCouponId('not-exists');
        $this->assertNull($notFound);
    }

    public function testFindAllWithLimitsReturnsArray(): void
    {
        $result = $this->repository->findAllWithLimits();
        $this->assertNotNull($result);
    }

    public function testFindAllWithEnabledLimitsReturnsArray(): void
    {
        $result = $this->repository->findAllWithEnabledLimits();
        $this->assertNotNull($result);
    }

    public function testIsCommandExistsChecksCorrectly(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('EXISTS123');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('EXISTS_TEST');
        $entity->setCoupon($coupon);
        $this->repository->save($entity, true);

        $exists = $this->repository->isCommandExists('EXISTS_TEST');
        $this->assertTrue($exists);

        $notExists = $this->repository->isCommandExists('NOT_EXISTS');
        $this->assertFalse($notExists);

        $excludedCheck = $this->repository->isCommandExists('EXISTS_TEST', $entity->getId());
        $this->assertFalse($excludedCheck);
    }

    public function testGetUsageStatsReturnsCorrectFormat(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('STATS123');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('STATS_TEST');
        $this->setupBidirectionalAssociation($entity, $coupon);
        $this->repository->save($entity, true);

        $entityId = $entity->getId();
        $this->assertNotNull($entityId, 'Entity ID should not be null after save');
        $stats = $this->repository->getUsageStats($entityId);
        $this->assertArrayHasKey('totalUsage', $stats);
        $this->assertArrayHasKey('successUsage', $stats);
        $this->assertArrayHasKey('failureUsage', $stats);
        $this->assertEquals(0, $stats['totalUsage']);
        $this->assertEquals(0, $stats['successUsage']);
        $this->assertEquals(0, $stats['failureUsage']);
    }

    public function testSaveAndRemoveEntityMethods(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('SAVE123');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('SAVE_REMOVE_TEST');
        $this->setupBidirectionalAssociation($entity, $coupon);

        $this->repository->save($entity, true);
        $saved = $this->repository->findByCommand('SAVE_REMOVE_TEST');
        $this->assertNotNull($saved);

        $this->repository->remove($entity);
        $removed = $this->repository->findByCommand('SAVE_REMOVE_TEST');
        $this->assertNull($removed);
    }

    protected function getRepository(): CommandConfigRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_COMMAND');

        // 不设置关联关系，因为测试框架会persist这个实体
        // 如果设置了关联，Doctrine会尝试cascade persist关联的实体
        // 但由于没有cascade配置，会导致错误

        return $commandConfig;
    }

    /**
     * 辅助方法：正确设置 CommandConfig 和 Coupon 之间的双向关联关系
     *
     * 注意：由于没有cascade配置，需要手动persist两个实体
     * owning side是Coupon，inverse side是CommandConfig
     */
    private function setupBidirectionalAssociation(CommandConfig $commandConfig, Coupon $coupon): void
    {
        // 先persist Coupon实体（避免外键约束问题）
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        // 设置双向关联
        $commandConfig->setCoupon($coupon);
        $coupon->setCommandConfig($commandConfig);

        // 再persist CommandConfig
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->persist($coupon); // 更新Coupon的关联
    }

    public function testRemoveMethod(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('REMOVE_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $entity = new CommandConfig();
        $entity->setCommand('REMOVE_COMMAND');
        $this->setupBidirectionalAssociation($entity, $coupon);
        $this->repository->save($entity, true);

        $this->assertNotNull($this->repository->findByCommand('REMOVE_COMMAND'));

        $this->repository->remove($entity);
        $this->assertNull($this->repository->findByCommand('REMOVE_COMMAND'));
    }
}
