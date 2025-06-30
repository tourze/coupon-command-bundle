<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCommandBundle\Repository\CommandLimitRepository;
use Tourze\CouponCommandBundle\Service\CommandManagementService;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CommandManagementServiceTest extends TestCase
{
    private CommandManagementService $service;
    /** @var MockObject&EntityManagerInterface */
    private MockObject $entityManager;
    /** @var MockObject&CommandConfigRepository */
    private MockObject $commandConfigRepository;
    /** @var MockObject&CommandLimitRepository */
    private MockObject $commandLimitRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->commandConfigRepository = $this->createMock(CommandConfigRepository::class);
        $this->commandLimitRepository = $this->createMock(CommandLimitRepository::class);

        $this->service = new CommandManagementService(
            $this->entityManager,
            $this->commandConfigRepository,
            $this->commandLimitRepository
        );
    }

    public function test_create_command_config_success(): void
    {
        $command = 'NEW_TEST_CMD';
        /** @var MockObject&Coupon $coupon */
        $coupon = $this->createMock(Coupon::class);

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('isCommandExists')
            ->with($command)
            ->willReturn(false);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommandConfig::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->createCommandConfig($command, $coupon);

        $this->assertInstanceOf(CommandConfig::class, $result);
        $this->assertEquals($command, $result->getCommand());
        $this->assertSame($coupon, $result->getCoupon());
    }

    public function test_create_command_config_with_duplicate_command(): void
    {
        $command = 'DUPLICATE_CMD';
        /** @var MockObject&Coupon $coupon */
        $coupon = $this->createMock(Coupon::class);

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('isCommandExists')
            ->with($command)
            ->willReturn(true);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('口令已存在');

        $this->service->createCommandConfig($command, $coupon);
    }

    public function test_update_command_config_success(): void
    {
        $id = 'config_123';
        $newCommand = 'UPDATED_CMD';

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('OLD_CMD');

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($commandConfig);

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('isCommandExists')
            ->with($newCommand, $id)
            ->willReturn(false);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($commandConfig);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->updateCommandConfig($id, $newCommand);

        $this->assertSame($commandConfig, $result);
        $this->assertEquals($newCommand, $result->getCommand());
    }

    public function test_update_command_config_not_found(): void
    {
        $id = 'non_existent_id';
        $newCommand = 'NEW_CMD';

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('口令配置不存在');

        $this->service->updateCommandConfig($id, $newCommand);
    }

    public function test_update_command_config_duplicate(): void
    {
        $id = 'config_123';
        $duplicateCommand = 'DUPLICATE_CMD';

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('OLD_CMD');

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($commandConfig);

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('isCommandExists')
            ->with($duplicateCommand, $id)
            ->willReturn(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('口令已存在');

        $this->service->updateCommandConfig($id, $duplicateCommand);
    }

    public function test_delete_command_config_success(): void
    {
        $id = 'config_123';
        $commandConfig = new CommandConfig();

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($commandConfig);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($commandConfig);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->deleteCommandConfig($id);

        $this->assertTrue($result);
    }

    public function test_delete_command_config_not_found(): void
    {
        $id = 'non_existent_id';

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $result = $this->service->deleteCommandConfig($id);

        $this->assertFalse($result);
    }

    public function test_add_command_limit_success(): void
    {
        $commandConfigId = 'config_123';
        $maxUsagePerUser = 5;
        $maxTotalUsage = 100;
        $startTime = new \DateTimeImmutable('2024-01-01');
        $endTime = new \DateTimeImmutable('2024-12-31');
        $allowedUsers = ['user1', 'user2'];
        $allowedUserTags = ['vip'];

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_CMD');

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandConfigId)
            ->willReturn($commandConfig);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommandLimit::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->addCommandLimit(
            $commandConfigId,
            $maxUsagePerUser,
            $maxTotalUsage,
            $startTime,
            $endTime,
            $allowedUsers,
            $allowedUserTags
        );

        $this->assertInstanceOf(CommandLimit::class, $result);
        $this->assertSame($commandConfig, $result->getCommandConfig());
        $this->assertEquals($maxUsagePerUser, $result->getMaxUsagePerUser());
        $this->assertEquals($maxTotalUsage, $result->getMaxTotalUsage());
        $this->assertEquals($startTime, $result->getStartTime());
        $this->assertEquals($endTime, $result->getEndTime());
        $this->assertEquals($allowedUsers, $result->getAllowedUsers());
        $this->assertEquals($allowedUserTags, $result->getAllowedUserTags());
    }

    public function test_add_command_limit_config_not_found(): void
    {
        $commandConfigId = 'non_existent_id';

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandConfigId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('口令配置不存在');

        $this->service->addCommandLimit($commandConfigId);
    }

    public function test_add_command_limit_already_exists(): void
    {
        $commandConfigId = 'config_123';
        $existingLimit = new CommandLimit();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommandLimit($existingLimit);

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandConfigId)
            ->willReturn($commandConfig);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('该口令已有限制配置');

        $this->service->addCommandLimit($commandConfigId);
    }

    public function test_update_command_limit_success(): void
    {
        $commandLimitId = 'limit_123';
        $newMaxUsagePerUser = 10;
        $newMaxTotalUsage = 200;
        $newIsEnabled = false;

        $commandLimit = new CommandLimit();
        $commandLimit->setMaxUsagePerUser(5);
        $commandLimit->setMaxTotalUsage(100);
        $commandLimit->setIsEnabled(true);

        $this->commandLimitRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandLimitId)
            ->willReturn($commandLimit);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($commandLimit);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->updateCommandLimit(
            $commandLimitId,
            $newMaxUsagePerUser,
            $newMaxTotalUsage,
            null,
            null,
            null,
            null,
            $newIsEnabled
        );

        $this->assertSame($commandLimit, $result);
        $this->assertEquals($newMaxUsagePerUser, $result->getMaxUsagePerUser());
        $this->assertEquals($newMaxTotalUsage, $result->getMaxTotalUsage());
        $this->assertEquals($newIsEnabled, $result->isEnabled());
    }

    public function test_update_command_limit_not_found(): void
    {
        $commandLimitId = 'non_existent_id';

        $this->commandLimitRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandLimitId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('限制配置不存在');

        $this->service->updateCommandLimit($commandLimitId);
    }

    public function test_delete_command_limit_success(): void
    {
        $commandLimitId = 'limit_123';
        $commandLimit = new CommandLimit();

        $this->commandLimitRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandLimitId)
            ->willReturn($commandLimit);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($commandLimit);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->deleteCommandLimit($commandLimitId);

        $this->assertTrue($result);
    }

    public function test_delete_command_limit_not_found(): void
    {
        $commandLimitId = 'non_existent_id';

        $this->commandLimitRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandLimitId)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $result = $this->service->deleteCommandLimit($commandLimitId);

        $this->assertFalse($result);
    }

    public function test_get_command_config_detail(): void
    {
        $id = 'config_123';
        $usageStats = [
            'totalUsage' => 50,
            'successUsage' => 45,
            'failureUsage' => 5,
        ];

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('DETAIL_TEST');

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($commandConfig);

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('getUsageStats')
            ->with($id)
            ->willReturn($usageStats);

        $result = $this->service->getCommandConfigDetail($id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertEquals($usageStats, $result['stats']);
        $this->assertEquals($commandConfig->retrieveApiArray(), $result['config']);
    }

    public function test_get_command_config_detail_not_found(): void
    {
        $id = 'non_existent_id';

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $result = $this->service->getCommandConfigDetail($id);

        $this->assertNull($result);
    }

    public function test_get_command_config_list(): void
    {
        $config1 = $this->createMock(CommandConfig::class);
        $config1->method('getId')->willReturn('config_1');
        $config1->method('retrieveApiArray')->willReturn(['id' => 'config_1', 'command' => 'CMD1']);
        
        $config2 = $this->createMock(CommandConfig::class);
        $config2->method('getId')->willReturn('config_2');
        $config2->method('retrieveApiArray')->willReturn(['id' => 'config_2', 'command' => 'CMD2']);

        $configs = [$config1, $config2];

        $stats1 = ['totalUsage' => 10, 'successUsage' => 8, 'failureUsage' => 2];
        $stats2 = ['totalUsage' => 20, 'successUsage' => 15, 'failureUsage' => 5];

        $this->commandConfigRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($configs);

        $this->commandConfigRepository
            ->expects($this->exactly(2))
            ->method('getUsageStats')
            ->willReturnMap([
                ['config_1', $stats1],
                ['config_2', $stats2],
            ]);

        $result = $this->service->getCommandConfigList();

        $this->assertCount(2, $result);
        
        $this->assertEquals(['id' => 'config_1', 'command' => 'CMD1'], $result[0]['config']);
        $this->assertEquals($stats1, $result[0]['stats']);
        
        $this->assertEquals(['id' => 'config_2', 'command' => 'CMD2'], $result[1]['config']);
        $this->assertEquals($stats2, $result[1]['stats']);
    }

    public function test_toggle_command_limit_status(): void
    {
        $commandLimitId = 'limit_123';

        $commandLimit = new CommandLimit();
        $commandLimit->setIsEnabled(true);

        $this->commandLimitRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandLimitId)
            ->willReturn($commandLimit);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($commandLimit);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->service->toggleCommandLimitStatus($commandLimitId);

        $this->assertSame($commandLimit, $result);
        $this->assertFalse($result->isEnabled()); // 应该被切换为 false
    }

    public function test_toggle_command_limit_status_not_found(): void
    {
        $commandLimitId = 'non_existent_id';

        $this->commandLimitRepository
            ->expects($this->once())
            ->method('find')
            ->with($commandLimitId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('限制配置不存在');

        $this->service->toggleCommandLimitStatus($commandLimitId);
    }
} 