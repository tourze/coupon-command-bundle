<?php

namespace Tourze\CouponCommandBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(CommandUsageRecord::class)]
final class CommandUsageRecordTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CommandUsageRecord();
    }

    /** @return iterable<array{0: string, 1: mixed}> */
    public static function propertiesProvider(): iterable
    {
        yield 'userId' => ['userId', 'test_user_123'];
        yield 'commandText' => ['commandText', 'TEST_COMMAND_2024'];
        yield 'couponId' => ['couponId', 'coupon_456'];
        yield 'success' => ['success', true];
        yield 'failureReason' => ['failureReason', '口令已过期'];
        yield 'extraData' => ['extraData', ['test' => 'data']];
        yield 'commandConfig' => ['commandConfig', new CommandConfig()];
        yield 'createTime' => ['createTime', new \DateTimeImmutable('2024-01-15 14:30:00')];
        yield 'createdBy' => ['createdBy', 'admin_user'];
        yield 'createdFromIp' => ['createdFromIp', '192.168.1.1'];
    }

    public function testUsageRecordCreation(): void
    {
        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $this->assertNull($usageRecord->getId());
        $this->assertNull($usageRecord->getUserId());
        $this->assertNull($usageRecord->getCommandText());
        $this->assertNull($usageRecord->getCouponId());
        $this->assertFalse($usageRecord->isSuccess());
        $this->assertNull($usageRecord->getFailureReason());
        $this->assertNull($usageRecord->getExtraData());
    }

    public function testSetAndGetUsageFields(): void
    {
        $userId = 'test_user_123';
        $commandText = 'TEST_COMMAND_2024';
        $couponId = 'coupon_456';

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setUserId($userId);
        $usageRecord->setCommandText($commandText);
        $usageRecord->setCouponId($couponId);

        $this->assertEquals($userId, $usageRecord->getUserId());
        $this->assertEquals($commandText, $usageRecord->getCommandText());
        $this->assertEquals($couponId, $usageRecord->getCouponId());
    }

    public function testSuccessFailureStatus(): void
    {
        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        // 测试默认状态
        $this->assertFalse($usageRecord->isSuccess());

        // 测试设置成功状态
        $usageRecord->setIsSuccess(true);
        $this->assertTrue($usageRecord->isSuccess());

        // 测试设置失败状态
        $usageRecord->setIsSuccess(false);
        $this->assertFalse($usageRecord->isSuccess());
    }

    public function testFailureReasonHandling(): void
    {
        $failureReason = '口令已过期';

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setFailureReason($failureReason);
        $this->assertEquals($failureReason, $usageRecord->getFailureReason());

        // 测试设置为 null
        $usageRecord->setFailureReason(null);
        $this->assertNull($usageRecord->getFailureReason());
    }

    public function testExtraDataHandling(): void
    {
        $extraData = [
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0...',
            'request_id' => 'req_123456',
        ];

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setExtraData($extraData);
        $this->assertEquals($extraData, $usageRecord->getExtraData());

        // 测试空数组
        $usageRecord->setExtraData([]);
        $this->assertEquals([], $usageRecord->getExtraData());

        // 测试设置为 null
        $usageRecord->setExtraData(null);
        $this->assertNull($usageRecord->getExtraData());
    }

    public function testCommandConfigRelationship(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_RELATIONSHIP');

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setCommandConfig($commandConfig);

        $this->assertSame($commandConfig, $usageRecord->getCommandConfig());

        // 测试设置为 null
        $usageRecord->setCommandConfig(null);
        $this->assertNull($usageRecord->getCommandConfig());
    }

    public function testTrackingFields(): void
    {
        $createdBy = 'admin_user';
        $createdFromIp = '10.0.0.1';

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setCreatedBy($createdBy);
        $usageRecord->setCreatedFromIp($createdFromIp);

        $this->assertEquals($createdBy, $usageRecord->getCreatedBy());
        $this->assertEquals($createdFromIp, $usageRecord->getCreatedFromIp());
    }

    public function testCreateTimeHandling(): void
    {
        $createTime = new \DateTimeImmutable('2024-01-15 14:30:00');

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setCreateTime($createTime);
        $this->assertEquals($createTime, $usageRecord->getCreateTime());

        // 测试设置为 null
        $usageRecord->setCreateTime(null);
        $this->assertNull($usageRecord->getCreateTime());
    }

    public function testRetrieveApiArray(): void
    {
        $userId = 'api_test_user';
        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $commandText = 'API_TEST_CMD';
        $couponId = 'api_coupon_123';
        $failureReason = 'API测试失败';
        $extraData = ['test' => 'data'];
        $createTime = new \DateTimeImmutable('2024-01-15 15:45:00');

        $usageRecord->setUserId($userId);
        $usageRecord->setCommandText($commandText);
        $usageRecord->setCouponId($couponId);
        $usageRecord->setIsSuccess(false);
        $usageRecord->setFailureReason($failureReason);
        $usageRecord->setExtraData($extraData);
        $usageRecord->setCreateTime($createTime);

        $apiArray = $usageRecord->retrieveApiArray();
        $this->assertEquals($userId, $apiArray['userId']);
        $this->assertEquals($commandText, $apiArray['commandText']);
        $this->assertEquals($couponId, $apiArray['couponId']);
        $this->assertFalse($apiArray['isSuccess']);
        $this->assertEquals($failureReason, $apiArray['failureReason']);
        $this->assertEquals($extraData, $apiArray['extraData']);
        $this->assertEquals('2024-01-15 15:45:00', $apiArray['createTime']);
        $this->assertArrayHasKey('id', $apiArray);
    }

    public function testSuccessfulUsageRecord(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('SUCCESS_TEST');

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setCommandConfig($commandConfig);
        $usageRecord->setUserId('success_user');
        $usageRecord->setCommandText('SUCCESS_TEST');
        $usageRecord->setCouponId('coupon_success_123');
        $usageRecord->setIsSuccess(true);

        $this->assertTrue($usageRecord->isSuccess());
        $this->assertNull($usageRecord->getFailureReason());
        $this->assertEquals('coupon_success_123', $usageRecord->getCouponId());
    }

    public function testFailedUsageRecord(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('FAILED_TEST');

        $usageRecord = $this->createEntity();
        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $usageRecord->setCommandConfig($commandConfig);
        $usageRecord->setUserId('failed_user');
        $usageRecord->setCommandText('FAILED_TEST');
        $usageRecord->setIsSuccess(false);
        $usageRecord->setFailureReason('口令使用次数已达上限');

        $this->assertFalse($usageRecord->isSuccess());
        $this->assertEquals('口令使用次数已达上限', $usageRecord->getFailureReason());
        $this->assertNull($usageRecord->getCouponId());
    }
}
