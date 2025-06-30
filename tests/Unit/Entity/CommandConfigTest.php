<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;

class CommandConfigTest extends TestCase
{
    private CommandConfig $commandConfig;

    protected function setUp(): void
    {
        $this->commandConfig = new CommandConfig();
    }

    public function test_command_config_creation(): void
    {
        $this->assertInstanceOf(CommandConfig::class, $this->commandConfig);
        $this->assertNull($this->commandConfig->getId());
        $this->assertNull($this->commandConfig->getCommand());
        $this->assertCount(0, $this->commandConfig->getUsageRecords());
    }

    public function test_set_and_get_command(): void
    {
        $command = 'TEST_COMMAND_2024';
        $this->commandConfig->setCommand($command);
        
        $this->assertEquals($command, $this->commandConfig->getCommand());
    }

    public function test_command_limit_relationship(): void
    {
        $commandLimit = new CommandLimit();
        $commandLimit->setMaxUsagePerUser(10);
        
        $this->commandConfig->setCommandLimit($commandLimit);
        
        $this->assertSame($commandLimit, $this->commandConfig->getCommandLimit());
        $this->assertSame($this->commandConfig, $commandLimit->getCommandConfig());
    }

    public function test_usage_records_collection(): void
    {
        $usageRecord1 = new CommandUsageRecord();
        $usageRecord1->setUserId('user1');
        $usageRecord1->setCommandText('TEST_CMD');
        $usageRecord1->setIsSuccess(true);
        
        $usageRecord2 = new CommandUsageRecord();
        $usageRecord2->setUserId('user2');
        $usageRecord2->setCommandText('TEST_CMD');
        $usageRecord2->setIsSuccess(false);
        
        // 添加使用记录
        $this->commandConfig->addUsageRecord($usageRecord1);
        $this->commandConfig->addUsageRecord($usageRecord2);
        
        $this->assertTrue($this->commandConfig->getUsageRecords()->contains($usageRecord1));
        $this->assertTrue($this->commandConfig->getUsageRecords()->contains($usageRecord2));
        $this->assertEquals(2, $this->commandConfig->getUsageRecords()->count());
        
        // 测试关联关系
        $this->assertSame($this->commandConfig, $usageRecord1->getCommandConfig());
        $this->assertSame($this->commandConfig, $usageRecord2->getCommandConfig());
        
        // 移除使用记录
        $this->commandConfig->removeUsageRecord($usageRecord1);
        $this->assertFalse($this->commandConfig->getUsageRecords()->contains($usageRecord1));
        $this->assertEquals(1, $this->commandConfig->getUsageRecords()->count());
    }

    public function test_duplicate_usage_record_not_added(): void
    {
        $usageRecord = new CommandUsageRecord();
        $usageRecord->setUserId('user1');
        
        $this->commandConfig->addUsageRecord($usageRecord);
        $this->commandConfig->addUsageRecord($usageRecord); // 重复添加
        
        $this->assertEquals(1, $this->commandConfig->getUsageRecords()->count());
    }

    public function test_timestamp_methods(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $updateTime = new \DateTimeImmutable('2024-01-01 11:00:00');
        
        $this->commandConfig->setCreateTime($createTime);
        $this->commandConfig->setUpdateTime($updateTime);
        
        $this->assertEquals($createTime, $this->commandConfig->getCreateTime());
        $this->assertEquals($updateTime, $this->commandConfig->getUpdateTime());
    }

    public function test_retrieve_api_array(): void
    {
        $this->commandConfig->setCommand('API_TEST');
        
        $createTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $this->commandConfig->setCreateTime($createTime);
        
        $apiArray = $this->commandConfig->retrieveApiArray();
        
        $this->assertEquals('API_TEST', $apiArray['command']);
        $this->assertEquals('2024-01-01 10:00:00', $apiArray['createTime']);
        $this->assertEquals(0, $apiArray['usageCount']);
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('updateTime', $apiArray);
        $this->assertArrayHasKey('commandLimit', $apiArray);
    }

    public function test_user_tracking_fields(): void
    {
        $createdBy = 'admin_user';
        $updatedBy = 'editor_user';
        $createdFromIp = '192.168.1.1';
        $updatedFromIp = '192.168.1.2';
        
        $this->commandConfig->setCreatedBy($createdBy);
        $this->commandConfig->setUpdatedBy($updatedBy);
        $this->commandConfig->setCreatedFromIp($createdFromIp);
        $this->commandConfig->setUpdatedFromIp($updatedFromIp);
        
        $this->assertEquals($createdBy, $this->commandConfig->getCreatedBy());
        $this->assertEquals($updatedBy, $this->commandConfig->getUpdatedBy());
        $this->assertEquals($createdFromIp, $this->commandConfig->getCreatedFromIp());
        $this->assertEquals($updatedFromIp, $this->commandConfig->getUpdatedFromIp());
    }
} 