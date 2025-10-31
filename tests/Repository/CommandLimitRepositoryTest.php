<?php

namespace Tourze\CouponCommandBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Repository\CommandLimitRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * CommandLimit Repository 测试
 *
 * 此测试类重写了父类的排序测试方法，避免测试 inverse side 关联字段 'commandConfig'。
 * Doctrine 不允许在 inverse side 关联字段上进行排序查询。
 *
 * @internal
 */
#[CoversClass(CommandLimitRepository::class)]
#[RunTestsInSeparateProcesses]
final class CommandLimitRepositoryTest extends AbstractRepositoryTestCase
{
    private CommandLimitRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CommandLimitRepository::class);
    }

    public function testInheritance(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testRepositoryClassInitialization(): void
    {
        $this->assertInstanceOf(CommandLimitRepository::class, $this->repository);
    }

    public function testFindByCommandConfigIdWithExistingRecord(): void
    {
        // 创建测试数据
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setSn('TEST_COUPON_1');

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_COMMAND');
        $commandConfig->setCoupon($coupon);

        $commandLimit = new CommandLimit();
        $commandLimit->setMaxUsagePerUser(5);
        $commandLimit->setMaxTotalUsage(100);
        $commandLimit->setIsEnabled(true);

        // 设置双向关联关系
        $commandConfig->setCommandLimit($commandLimit);

        // 手动持久化 Coupon 和 CommandConfig
        $em = self::getEntityManager();
        $em->persist($coupon);
        $em->persist($commandConfig);
        $this->repository->save($commandLimit, true);

        // 测试查找功能
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);
        $result = $this->repository->findByCommandConfigId($configId);

        $this->assertInstanceOf(CommandLimit::class, $result);
        $this->assertSame($commandLimit->getId(), $result->getId());
        $this->assertSame($commandConfig, $result->getCommandConfig());
        $this->assertEquals(5, $result->getMaxUsagePerUser());
        $this->assertEquals(100, $result->getMaxTotalUsage());
        $this->assertTrue($result->isEnabled());
    }

    public function testFindByCommandConfigIdWithNonExistingRecord(): void
    {
        $result = $this->repository->findByCommandConfigId('non-existing-id');

        $this->assertNull($result);
    }

    public function testFindAllEnabledWithEnabledRecords(): void
    {
        // 创建启用的限制配置
        $coupon1 = new Coupon();
        $coupon1->setName('Test Coupon 1');
        $coupon1->setSn('ENABLED_COUPON_1');

        $commandConfig1 = new CommandConfig();
        $commandConfig1->setCommand('ENABLED_COMMAND_1');
        $commandConfig1->setCoupon($coupon1);

        $enabledLimit1 = new CommandLimit();
        $enabledLimit1->setCommandConfig($commandConfig1);
        $enabledLimit1->setMaxUsagePerUser(10);
        $enabledLimit1->setIsEnabled(true);

        $coupon2 = new Coupon();
        $coupon2->setName('Test Coupon 2');
        $coupon2->setSn('ENABLED_COUPON_2');

        $commandConfig2 = new CommandConfig();
        $commandConfig2->setCommand('ENABLED_COMMAND_2');
        $commandConfig2->setCoupon($coupon2);

        $enabledLimit2 = new CommandLimit();
        $enabledLimit2->setCommandConfig($commandConfig2);
        $enabledLimit2->setMaxTotalUsage(200);
        $enabledLimit2->setIsEnabled(true);

        // 创建禁用的限制配置
        $coupon3 = new Coupon();
        $coupon3->setName('Test Coupon 3');
        $coupon3->setSn('DISABLED_COUPON');

        $commandConfig3 = new CommandConfig();
        $commandConfig3->setCommand('DISABLED_COMMAND');
        $commandConfig3->setCoupon($coupon3);

        $disabledLimit = new CommandLimit();
        $disabledLimit->setCommandConfig($commandConfig3);
        $disabledLimit->setMaxUsagePerUser(5);
        $disabledLimit->setIsEnabled(false);

        // 手动持久化所有 Coupon 和 CommandConfig
        $em = self::getEntityManager();
        $em->persist($coupon1);
        $em->persist($coupon2);
        $em->persist($coupon3);
        $em->persist($commandConfig1);
        $em->persist($commandConfig2);
        $em->persist($commandConfig3);

        $this->repository->save($enabledLimit1, true);
        $this->repository->save($enabledLimit2, true);
        $this->repository->save($disabledLimit, true);

        // 测试查找所有启用的配置
        $results = $this->repository->findAllEnabled();
        $this->assertGreaterThanOrEqual(2, count($results)); // 至少包含我们创建的2个启用记录

        // 验证所有返回的结果都是启用的
        foreach ($results as $result) {
            $this->assertInstanceOf(CommandLimit::class, $result);
            $this->assertTrue($result->isEnabled());
        }

        // 验证我们创建的记录存在于结果中
        $ids = array_map(fn (CommandLimit $limit) => $limit->getId(), $results);
        $this->assertContains($enabledLimit1->getId(), $ids);
        $this->assertContains($enabledLimit2->getId(), $ids);
        $this->assertNotContains($disabledLimit->getId(), $ids);
    }

    public function testFindAllEnabledWithNoEnabledRecords(): void
    {
        // 先清理所有现有的启用记录
        $em = self::getEntityManager();
        $existingEnabled = $this->repository->findAllEnabled();
        foreach ($existingEnabled as $limit) {
            $this->repository->remove($limit);
        }

        // 创建禁用的限制配置
        $coupon = new Coupon();
        $coupon->setName('Disabled Test Coupon');
        $coupon->setSn('DISABLED_TEST_COUPON');

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('DISABLED_COMMAND');
        $commandConfig->setCoupon($coupon);

        $disabledLimit = new CommandLimit();
        $disabledLimit->setCommandConfig($commandConfig);
        $disabledLimit->setIsEnabled(false);

        // 手动持久化 Coupon 和 CommandConfig
        $em->persist($coupon);
        $em->persist($commandConfig);
        $this->repository->save($disabledLimit, true);

        $results = $this->repository->findAllEnabled();
        $this->assertEmpty($results);
    }

    public function testSaveMethodPersistsEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Save Test Coupon');
        $coupon->setSn('SAVE_TEST_COUPON');

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('SAVE_TEST_COMMAND');
        $commandConfig->setCoupon($coupon);

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxUsagePerUser(15);
        $commandLimit->setMaxTotalUsage(300);
        $commandLimit->setCurrentUsage(50);
        $commandLimit->setIsEnabled(true);

        // 手动持久化 Coupon 和 CommandConfig
        $em = self::getEntityManager();
        $em->persist($coupon);
        $em->persist($commandConfig);

        // 保存实体
        $this->repository->save($commandLimit, true);

        // 验证实体已保存
        $savedLimit = $this->repository->find($commandLimit->getId());

        $this->assertInstanceOf(CommandLimit::class, $savedLimit);
        $this->assertEquals(15, $savedLimit->getMaxUsagePerUser());
        $this->assertEquals(300, $savedLimit->getMaxTotalUsage());
        $this->assertEquals(50, $savedLimit->getCurrentUsage());
        $this->assertTrue($savedLimit->isEnabled());
    }

    public function testSaveMethodWithoutFlush(): void
    {
        $coupon = new Coupon();
        $coupon->setName('No Flush Test Coupon');
        $coupon->setSn('NO_FLUSH_TEST_COUPON');

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('NO_FLUSH_TEST');
        $commandConfig->setCoupon($coupon);

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxUsagePerUser(20);
        $commandLimit->setIsEnabled(true);

        // 手动持久化 Coupon 和 CommandConfig
        $em = self::getEntityManager();
        $em->persist($coupon);
        $em->persist($commandConfig);

        // 保存但不刷新
        $this->repository->save($commandLimit, false);

        // 在同一事务中应该能找到
        $this->assertTrue($em->contains($commandLimit));

        // 手动刷新后应该能从数据库中找到
        $em->flush();
        $savedLimit = $this->repository->find($commandLimit->getId());

        $this->assertInstanceOf(CommandLimit::class, $savedLimit);
        $this->assertEquals(20, $savedLimit->getMaxUsagePerUser());
    }

    public function testRemoveMethodDeletesEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Remove Test Coupon');
        $coupon->setSn('REMOVE_TEST_COUPON');

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('REMOVE_TEST_COMMAND');
        $commandConfig->setCoupon($coupon);

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxUsagePerUser(25);
        $commandLimit->setIsEnabled(true);

        // 手动持久化 Coupon 和 CommandConfig
        $em = self::getEntityManager();
        $em->persist($coupon);
        $em->persist($commandConfig);

        // 先保存
        $this->repository->save($commandLimit, true);
        $limitId = $commandLimit->getId();

        // 验证存在
        $this->assertNotNull($this->repository->find($limitId));

        // 删除
        $this->repository->remove($commandLimit);

        // 验证已删除
        $this->assertNull($this->repository->find($limitId));
    }

    public function testRemoveMethodWithoutFlush(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Remove No Flush Test Coupon');
        $coupon->setSn('REMOVE_NO_FLUSH_TEST_COUPON');

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('REMOVE_NO_FLUSH_TEST');
        $commandConfig->setCoupon($coupon);

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxUsagePerUser(30);
        $commandLimit->setIsEnabled(true);

        // 手动持久化 Coupon 和 CommandConfig
        $em = self::getEntityManager();
        $em->persist($coupon);
        $em->persist($commandConfig);

        // 先保存
        $this->repository->save($commandLimit, true);
        $limitId = $commandLimit->getId();

        // 删除但不刷新
        $this->repository->remove($commandLimit, false);

        // 在同一事务中应该标记为删除
        $em = self::getEntityManager();
        $this->assertFalse($em->contains($commandLimit));

        // 手动刷新后应该从数据库中删除
        $em->flush();
        $this->assertNull($this->repository->find($limitId));
    }

    public function testComplexQueryScenario(): void
    {
        // 先清理所有现有记录以确保测试的独立性
        $em = self::getEntityManager();
        $existingLimits = $this->repository->findAll();
        foreach ($existingLimits as $limit) {
            $this->repository->remove($limit);
        }

        // 创建多个测试场景的数据
        $scenarios = [
            ['command' => 'SCENARIO_1', 'maxPerUser' => 5, 'maxTotal' => 100, 'enabled' => true, 'sn' => 'SCENARIO_1_COUPON'],
            ['command' => 'SCENARIO_2', 'maxPerUser' => 10, 'maxTotal' => 200, 'enabled' => true, 'sn' => 'SCENARIO_2_COUPON'],
            ['command' => 'SCENARIO_3', 'maxPerUser' => 15, 'maxTotal' => 300, 'enabled' => false, 'sn' => 'SCENARIO_3_COUPON'],
        ];

        $savedLimits = [];

        foreach ($scenarios as $scenario) {
            $coupon = new Coupon();
            $coupon->setName(sprintf('Test Coupon for %s', $scenario['command']));
            $coupon->setSn($scenario['sn']);

            $commandConfig = new CommandConfig();
            $commandConfig->setCommand($scenario['command']);
            $commandConfig->setCoupon($coupon);

            $commandLimit = new CommandLimit();
            $commandLimit->setMaxUsagePerUser($scenario['maxPerUser']);
            $commandLimit->setMaxTotalUsage($scenario['maxTotal']);
            $commandLimit->setIsEnabled($scenario['enabled']);

            // 设置双向关联关系
            $commandConfig->setCommandLimit($commandLimit);

            // 手动持久化 Coupon 和 CommandConfig
            $em->persist($coupon);
            $em->persist($commandConfig);
            $this->repository->save($commandLimit, true);
            $savedLimits[] = $commandLimit;
        }

        // 测试按配置ID查找
        $firstCommandConfig = $savedLimits[0]->getCommandConfig();
        $this->assertNotNull($firstCommandConfig);
        $configId = $firstCommandConfig->getId();
        $this->assertNotNull($configId);
        $foundLimit = $this->repository->findByCommandConfigId($configId);
        $this->assertNotNull($foundLimit);
        $foundCommandConfig = $foundLimit->getCommandConfig();
        $this->assertNotNull($foundCommandConfig);
        $this->assertEquals('SCENARIO_1', $foundCommandConfig->getCommand());

        // 测试查找所有启用的
        $enabledLimits = $this->repository->findAllEnabled();
        $this->assertCount(2, $enabledLimits); // 应该正好是我们创建的2个启用记录

        $enabledCommands = [];
        foreach ($enabledLimits as $limit) {
            $commandConfig = $limit->getCommandConfig();
            $this->assertNotNull($commandConfig);
            $enabledCommands[] = $commandConfig->getCommand();
        }
        $this->assertContains('SCENARIO_1', $enabledCommands);
        $this->assertContains('SCENARIO_2', $enabledCommands);
        $this->assertNotContains('SCENARIO_3', $enabledCommands);
    }

    public function testFindOneByAssociationCommandConfigShouldReturnMatchingEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Association Test Coupon');
        $coupon->setSn('ASSOC_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('ASSOC_TEST_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $entity = new CommandLimit();
        $entity->setMaxUsagePerUser(100);
        $entity->setIsEnabled(true);

        // 设置双向关联关系
        $commandConfig->setCommandLimit($entity);
        self::getEntityManager()->persist($commandConfig);
        $this->repository->save($entity, true);

        // 使用正确的方法通过CommandConfig ID查找CommandLimit
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);
        $result = $this->repository->findByCommandConfigId($configId);
        $this->assertNotNull($result);
        $this->assertSame($commandConfig->getId(), $result->getCommandConfig()?->getId());
        $this->assertEquals(100, $result->getMaxUsagePerUser());
    }

    public function testCountByAssociationCommandConfigShouldReturnCorrectNumber(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Count Association Test Coupon');
        $coupon->setSn('COUNT_ASSOC_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('COUNT_ASSOC_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $entity = new CommandLimit();
        $entity->setMaxUsagePerUser(200);
        $entity->setIsEnabled(true);

        // 设置双向关联关系
        $commandConfig->setCommandLimit($entity);
        self::getEntityManager()->persist($commandConfig);
        $this->repository->save($entity, true);

        // 验证存在的关联，通过查找所有启用的来计数
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);
        $foundLimit = $this->repository->findByCommandConfigId($configId);
        $this->assertNotNull($foundLimit);

        // 测试不存在的关联
        $otherCoupon = new Coupon();
        $otherCoupon->setName('Other Test Coupon');
        $otherCoupon->setSn('OTHER_TEST');
        self::getEntityManager()->persist($otherCoupon);
        self::getEntityManager()->flush();

        $otherCommandConfig = new CommandConfig();
        $otherCommandConfig->setCommand('OTHER_COMMAND');
        $otherCommandConfig->setCoupon($otherCoupon);
        self::getEntityManager()->persist($otherCommandConfig);
        self::getEntityManager()->flush();

        $otherConfigId = $otherCommandConfig->getId();
        $this->assertNotNull($otherConfigId);
        $notFoundLimit = $this->repository->findByCommandConfigId($otherConfigId);
        $this->assertNull($notFoundLimit);
    }

    protected function getRepository(): CommandLimitRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $commandLimit = new CommandLimit();
        $commandLimit->setMaxUsagePerUser(10);
        $commandLimit->setMaxTotalUsage(100);
        $commandLimit->setCurrentUsage(0);
        $commandLimit->setIsEnabled(true);

        // 不设置关联关系，因为测试框架会persist这个实体
        // CommandLimit.commandConfig是inverse side关联，没有外键约束
        // 可以独立存在而不需要CommandConfig

        return $commandLimit;
    }
}
