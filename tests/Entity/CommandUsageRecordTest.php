<?php

namespace Tourze\CouponCommandBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;

class CommandUsageRecordTest extends TestCase
{
    private CommandUsageRecord $usageRecord;

    protected function setUp(): void
    {
        $this->usageRecord = new CommandUsageRecord();
    }

    public function test_usage_record_creation(): void
    {
        $this->assertInstanceOf(CommandUsageRecord::class, $this->usageRecord);
        $this->assertNull($this->usageRecord->getId());
        $this->assertNull($this->usageRecord->getUserId());
        $this->assertNull($this->usageRecord->getCommandText());
        $this->assertNull($this->usageRecord->getCouponId());
        $this->assertFalse($this->usageRecord->isSuccess());
        $this->assertNull($this->usageRecord->getFailureReason());
        $this->assertNull($this->usageRecord->getExtraData());
    }

    public function test_set_and_get_usage_fields(): void
    {
        $userId = 'test_user_123';
        $commandText = 'TEST_COMMAND_2024';
        $couponId = 'coupon_456';

        $this->usageRecord->setUserId($userId);
        $this->usageRecord->setCommandText($commandText);
        $this->usageRecord->setCouponId($couponId);

        $this->assertEquals($userId, $this->usageRecord->getUserId());
        $this->assertEquals($commandText, $this->usageRecord->getCommandText());
        $this->assertEquals($couponId, $this->usageRecord->getCouponId());
    }

    public function test_success_failure_status(): void
    {
        // 测试默认状态
        $this->assertFalse($this->usageRecord->isSuccess());

        // 测试设置成功状态
        $this->usageRecord->setIsSuccess(true);
        $this->assertTrue($this->usageRecord->isSuccess());

        // 测试设置失败状态
        $this->usageRecord->setIsSuccess(false);
        $this->assertFalse($this->usageRecord->isSuccess());
    }

    public function test_failure_reason_handling(): void
    {
        $failureReason = '口令已过期';

        $this->usageRecord->setFailureReason($failureReason);
        $this->assertEquals($failureReason, $this->usageRecord->getFailureReason());

        // 测试设置为 null
        $this->usageRecord->setFailureReason(null);
        $this->assertNull($this->usageRecord->getFailureReason());
    }

    public function test_extra_data_handling(): void
    {
        $extraData = [
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0...',
            'request_id' => 'req_123456'
        ];

        $this->usageRecord->setExtraData($extraData);
        $this->assertEquals($extraData, $this->usageRecord->getExtraData());

        // 测试空数组
        $this->usageRecord->setExtraData([]);
        $this->assertEquals([], $this->usageRecord->getExtraData());

        // 测试设置为 null
        $this->usageRecord->setExtraData(null);
        $this->assertNull($this->usageRecord->getExtraData());
    }

    public function test_command_config_relationship(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_RELATIONSHIP');

        $this->usageRecord->setCommandConfig($commandConfig);

        $this->assertSame($commandConfig, $this->usageRecord->getCommandConfig());

        // 测试设置为 null
        $this->usageRecord->setCommandConfig(null);
        $this->assertNull($this->usageRecord->getCommandConfig());
    }

    public function test_tracking_fields(): void
    {
        $createdBy = 'admin_user';
        $createdFromIp = '10.0.0.1';

        $this->usageRecord->setCreatedBy($createdBy);
        $this->usageRecord->setCreatedFromIp($createdFromIp);

        $this->assertEquals($createdBy, $this->usageRecord->getCreatedBy());
        $this->assertEquals($createdFromIp, $this->usageRecord->getCreatedFromIp());
    }

    public function test_create_time_handling(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-15 14:30:00');

        $this->usageRecord->setCreateTime($createTime);
        $this->assertEquals($createTime, $this->usageRecord->getCreateTime());

        // 测试设置为 null
        $this->usageRecord->setCreateTime(null);
        $this->assertNull($this->usageRecord->getCreateTime());
    }

    public function test_retrieve_api_array(): void
    {
        $userId = 'api_test_user';
        $commandText = 'API_TEST_CMD';
        $couponId = 'api_coupon_123';
        $failureReason = 'API测试失败';
        $extraData = ['test' => 'data'];
        $createTime = new \DateTimeImmutable('2024-01-15 15:45:00');

        $this->usageRecord->setUserId($userId);
        $this->usageRecord->setCommandText($commandText);
        $this->usageRecord->setCouponId($couponId);
        $this->usageRecord->setIsSuccess(false);
        $this->usageRecord->setFailureReason($failureReason);
        $this->usageRecord->setExtraData($extraData);
        $this->usageRecord->setCreateTime($createTime);

        $apiArray = $this->usageRecord->retrieveApiArray();
        $this->assertEquals($userId, $apiArray['userId']);
        $this->assertEquals($commandText, $apiArray['commandText']);
        $this->assertEquals($couponId, $apiArray['couponId']);
        $this->assertFalse($apiArray['isSuccess']);
        $this->assertEquals($failureReason, $apiArray['failureReason']);
        $this->assertEquals($extraData, $apiArray['extraData']);
        $this->assertEquals('2024-01-15 15:45:00', $apiArray['createTime']);
        $this->assertArrayHasKey('id', $apiArray);
    }

    public function test_successful_usage_record(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('SUCCESS_TEST');

        $this->usageRecord->setCommandConfig($commandConfig);
        $this->usageRecord->setUserId('success_user');
        $this->usageRecord->setCommandText('SUCCESS_TEST');
        $this->usageRecord->setCouponId('coupon_success_123');
        $this->usageRecord->setIsSuccess(true);

        $this->assertTrue($this->usageRecord->isSuccess());
        $this->assertNull($this->usageRecord->getFailureReason());
        $this->assertEquals('coupon_success_123', $this->usageRecord->getCouponId());
    }

    public function test_failed_usage_record(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('FAILED_TEST');

        $this->usageRecord->setCommandConfig($commandConfig);
        $this->usageRecord->setUserId('failed_user');
        $this->usageRecord->setCommandText('FAILED_TEST');
        $this->usageRecord->setIsSuccess(false);
        $this->usageRecord->setFailureReason('口令使用次数已达上限');

        $this->assertFalse($this->usageRecord->isSuccess());
        $this->assertEquals('口令使用次数已达上限', $this->usageRecord->getFailureReason());
        $this->assertNull($this->usageRecord->getCouponId());
    }
}
