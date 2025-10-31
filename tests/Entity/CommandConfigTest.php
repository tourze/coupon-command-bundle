<?php

namespace Tourze\CouponCommandBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(CommandConfig::class)]
final class CommandConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CommandConfig();
    }

    /** @return iterable<array{0: string, 1: mixed}> */
    public static function propertiesProvider(): iterable
    {
        yield 'command' => ['command', 'TEST_COMMAND_2024'];
        yield 'coupon' => ['coupon', new Coupon()];
        yield 'commandLimit' => ['commandLimit', new CommandLimit()];
        yield 'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 11:00:00')];
        yield 'createdBy' => ['createdBy', 'admin_user'];
        yield 'updatedBy' => ['updatedBy', 'editor_user'];
        yield 'createdFromIp' => ['createdFromIp', '192.168.1.1'];
        yield 'updatedFromIp' => ['updatedFromIp', '192.168.1.2'];
    }

    public function testCommandConfigCreation(): void
    {
        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $this->assertNull($commandConfig->getId());
        $this->assertNull($commandConfig->getCommand());
        $this->assertCount(0, $commandConfig->getUsageRecords());
    }

    public function testSetAndGetCommand(): void
    {
        $command = 'TEST_COMMAND_2024';
        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $commandConfig->setCommand($command);

        $this->assertEquals($command, $commandConfig->getCommand());
    }

    public function testCommandLimitRelationship(): void
    {
        $commandLimit = new CommandLimit();
        $commandLimit->setMaxUsagePerUser(10);

        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $commandConfig->setCommandLimit($commandLimit);

        $this->assertSame($commandLimit, $commandConfig->getCommandLimit());
        $this->assertSame($commandConfig, $commandLimit->getCommandConfig());
    }

    public function testUsageRecordsCollection(): void
    {
        $usageRecord1 = new CommandUsageRecord();
        $usageRecord1->setUserId('user1');
        $usageRecord1->setCommandText('TEST_CMD');
        $usageRecord1->setIsSuccess(true);

        $usageRecord2 = new CommandUsageRecord();
        $usageRecord2->setUserId('user2');
        $usageRecord2->setCommandText('TEST_CMD');
        $usageRecord2->setIsSuccess(false);

        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);

        // 添加使用记录
        $commandConfig->addUsageRecord($usageRecord1);
        $commandConfig->addUsageRecord($usageRecord2);

        $this->assertTrue($commandConfig->getUsageRecords()->contains($usageRecord1));
        $this->assertTrue($commandConfig->getUsageRecords()->contains($usageRecord2));
        $this->assertEquals(2, $commandConfig->getUsageRecords()->count());

        // 测试关联关系
        $this->assertSame($commandConfig, $usageRecord1->getCommandConfig());
        $this->assertSame($commandConfig, $usageRecord2->getCommandConfig());

        // 移除使用记录
        $commandConfig->removeUsageRecord($usageRecord1);
        $this->assertFalse($commandConfig->getUsageRecords()->contains($usageRecord1));
        $this->assertEquals(1, $commandConfig->getUsageRecords()->count());
    }

    public function testDuplicateUsageRecordNotAdded(): void
    {
        $usageRecord = new CommandUsageRecord();
        $usageRecord->setUserId('user1');

        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $commandConfig->addUsageRecord($usageRecord);
        $commandConfig->addUsageRecord($usageRecord); // 重复添加

        $this->assertEquals(1, $commandConfig->getUsageRecords()->count());
    }

    public function testTimestampMethods(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-01 11:00:00');

        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $commandConfig->setCreateTime($createTime);
        $commandConfig->setUpdateTime($updateTime);

        $this->assertEquals($createTime, $commandConfig->getCreateTime());
        $this->assertEquals($updateTime, $commandConfig->getUpdateTime());
    }

    public function testRetrieveApiArray(): void
    {
        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $commandConfig->setCommand('API_TEST');

        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $commandConfig->setCreateTime($createTime);

        $apiArray = $commandConfig->retrieveApiArray();
        $this->assertEquals('API_TEST', $apiArray['command']);
        $this->assertEquals('2024-01-01 10:00:00', $apiArray['createTime']);
        $this->assertEquals(0, $apiArray['usageCount']);
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('updateTime', $apiArray);
        $this->assertArrayHasKey('commandLimit', $apiArray);
    }

    public function testUserTrackingFields(): void
    {
        $createdBy = 'admin_user';
        $updatedBy = 'editor_user';
        $createdFromIp = '192.168.1.1';
        $updatedFromIp = '192.168.1.2';

        $commandConfig = $this->createEntity();
        $this->assertInstanceOf(CommandConfig::class, $commandConfig);
        $commandConfig->setCreatedBy($createdBy);
        $commandConfig->setUpdatedBy($updatedBy);
        $commandConfig->setCreatedFromIp($createdFromIp);
        $commandConfig->setUpdatedFromIp($updatedFromIp);

        $this->assertEquals($createdBy, $commandConfig->getCreatedBy());
        $this->assertEquals($updatedBy, $commandConfig->getUpdatedBy());
        $this->assertEquals($createdFromIp, $commandConfig->getCreatedFromIp());
        $this->assertEquals($updatedFromIp, $commandConfig->getUpdatedFromIp());
    }
}
