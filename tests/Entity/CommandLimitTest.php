<?php

namespace Tourze\CouponCommandBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(CommandLimit::class)]
final class CommandLimitTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new CommandLimit();
    }

    /** @return iterable<array{0: string, 1: mixed}> */
    public static function propertiesProvider(): iterable
    {
        yield 'maxUsagePerUser' => ['maxUsagePerUser', 5];
        yield 'maxTotalUsage' => ['maxTotalUsage', 100];
        yield 'currentUsage' => ['currentUsage', 10];
        yield 'startTime' => ['startTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'endTime' => ['endTime', new \DateTimeImmutable('2024-12-31 23:59:59')];
        yield 'allowedUsers' => ['allowedUsers', ['user1', 'user2']];
        yield 'allowedUserTags' => ['allowedUserTags', ['vip', 'premium']];
        yield 'enabled' => ['enabled', true];
        yield 'commandConfig' => ['commandConfig', new CommandConfig()];
        yield 'createTime' => ['createTime', new \DateTimeImmutable('2024-01-01 09:00:00')];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable('2024-01-01 10:00:00')];
        yield 'createdBy' => ['createdBy', 'admin_user'];
        yield 'updatedBy' => ['updatedBy', 'editor_user'];
        yield 'createdFromIp' => ['createdFromIp', '192.168.1.1'];
        yield 'updatedFromIp' => ['updatedFromIp', '192.168.1.2'];
    }

    public function testCommandLimitCreation(): void
    {
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $this->assertNull($commandLimit->getId());
        $this->assertNull($commandLimit->getMaxUsagePerUser());
        $this->assertNull($commandLimit->getMaxTotalUsage());
        $this->assertEquals(0, $commandLimit->getCurrentUsage());
        $this->assertTrue($commandLimit->isEnabled());
    }

    public function testUsageLimits(): void
    {
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setMaxUsagePerUser(5);
        $commandLimit->setMaxTotalUsage(100);
        $commandLimit->setCurrentUsage(10);

        $this->assertEquals(5, $commandLimit->getMaxUsagePerUser());
        $this->assertEquals(100, $commandLimit->getMaxTotalUsage());
        $this->assertEquals(10, $commandLimit->getCurrentUsage());
    }

    public function testIncrementUsage(): void
    {
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setCurrentUsage(5);
        $commandLimit->incrementUsage();

        $this->assertEquals(6, $commandLimit->getCurrentUsage());

        $commandLimit->incrementUsage();
        $this->assertEquals(7, $commandLimit->getCurrentUsage());
    }

    public function testTimeConstraints(): void
    {
        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $endTime = new \DateTimeImmutable('2024-12-31 23:59:59');

        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setStartTime($startTime);
        $commandLimit->setEndTime($endTime);

        $this->assertEquals($startTime, $commandLimit->getStartTime());
        $this->assertEquals($endTime, $commandLimit->getEndTime());
    }

    public function testUserRestrictions(): void
    {
        $allowedUsers = ['user1', 'user2', 'user3'];
        $allowedUserTags = ['vip', 'premium'];

        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setAllowedUsers($allowedUsers);
        $commandLimit->setAllowedUserTags($allowedUserTags);

        $this->assertEquals($allowedUsers, $commandLimit->getAllowedUsers());
        $this->assertEquals($allowedUserTags, $commandLimit->getAllowedUserTags());
    }

    public function testEnabledStatus(): void
    {
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $this->assertTrue($commandLimit->isEnabled());

        $commandLimit->setIsEnabled(false);
        $this->assertFalse($commandLimit->isEnabled());

        $commandLimit->setIsEnabled(true);
        $this->assertTrue($commandLimit->isEnabled());
    }

    public function testIsTimeValidWithNoConstraints(): void
    {
        // 没有时间限制，应该总是有效
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $this->assertTrue($commandLimit->isTimeValid());
    }

    public function testIsTimeValidWithStartTimeOnly(): void
    {
        // 只设置开始时间
        $pastTime = new \DateTimeImmutable('-1 hour');
        $futureTime = new \DateTimeImmutable('+1 hour');

        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setStartTime($pastTime);
        $this->assertTrue($commandLimit->isTimeValid());

        $commandLimit->setStartTime($futureTime);
        $this->assertFalse($commandLimit->isTimeValid());
    }

    public function testIsTimeValidWithEndTimeOnly(): void
    {
        // 只设置结束时间
        $pastTime = new \DateTimeImmutable('-1 hour');
        $futureTime = new \DateTimeImmutable('+1 hour');

        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setEndTime($futureTime);
        $this->assertTrue($commandLimit->isTimeValid());

        $commandLimit->setEndTime($pastTime);
        $this->assertFalse($commandLimit->isTimeValid());
    }

    public function testIsTimeValidWithBothConstraints(): void
    {
        // 设置时间窗口
        $startTime = new \DateTimeImmutable('-1 hour');
        $endTime = new \DateTimeImmutable('+1 hour');

        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setStartTime($startTime);
        $commandLimit->setEndTime($endTime);
        $this->assertTrue($commandLimit->isTimeValid());

        // 过期的时间窗口
        $expiredStart = new \DateTimeImmutable('-2 hours');
        $expiredEnd = new \DateTimeImmutable('-1 hour');

        $commandLimit->setStartTime($expiredStart);
        $commandLimit->setEndTime($expiredEnd);
        $this->assertFalse($commandLimit->isTimeValid());
    }

    public function testHasTotalUsageQuotaWithNoLimit(): void
    {
        // 没有总量限制
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setMaxTotalUsage(null);
        $commandLimit->setCurrentUsage(999999);
        $this->assertTrue($commandLimit->hasTotalUsageQuota());
    }

    public function testHasTotalUsageQuotaWithLimit(): void
    {
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setMaxTotalUsage(100);

        // 未达到限制
        $commandLimit->setCurrentUsage(50);
        $this->assertTrue($commandLimit->hasTotalUsageQuota());

        // 达到限制
        $commandLimit->setCurrentUsage(100);
        $this->assertFalse($commandLimit->hasTotalUsageQuota());

        // 超过限制
        $commandLimit->setCurrentUsage(150);
        $this->assertFalse($commandLimit->hasTotalUsageQuota());
    }

    public function testIsUserAllowedWithNoRestrictions(): void
    {
        // 没有用户限制
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setAllowedUsers(null);
        $this->assertTrue($commandLimit->isUserAllowed('any_user'));
    }

    public function testIsUserAllowedWithWhitelist(): void
    {
        $allowedUsers = ['user1', 'user2', 'user3'];
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setAllowedUsers($allowedUsers);

        $this->assertTrue($commandLimit->isUserAllowed('user1'));
        $this->assertTrue($commandLimit->isUserAllowed('user2'));
        $this->assertFalse($commandLimit->isUserAllowed('user4'));
    }

    public function testCommandConfigRelationship(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_LIMIT');

        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setCommandConfig($commandConfig);

        $this->assertSame($commandConfig, $commandLimit->getCommandConfig());
    }

    public function testRetrieveApiArray(): void
    {
        $commandLimit = $this->createEntity();
        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $commandLimit->setMaxUsagePerUser(5);
        $commandLimit->setMaxTotalUsage(100);
        $commandLimit->setCurrentUsage(25);
        $commandLimit->setAllowedUsers(['user1', 'user2']);
        $commandLimit->setAllowedUserTags(['vip']);

        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $createTime = new \DateTimeImmutable('2024-01-01 09:00:00');

        $commandLimit->setStartTime($startTime);
        $commandLimit->setCreateTime($createTime);

        $apiArray = $commandLimit->retrieveApiArray();
        $this->assertEquals(5, $apiArray['maxUsagePerUser']);
        $this->assertEquals(100, $apiArray['maxTotalUsage']);
        $this->assertEquals(25, $apiArray['currentUsage']);
        $this->assertEquals(['user1', 'user2'], $apiArray['allowedUsers']);
        $this->assertEquals(['vip'], $apiArray['allowedUserTags']);
        $this->assertEquals('2024-01-01 10:00:00', $apiArray['startTime']);
        $this->assertEquals('2024-01-01 09:00:00', $apiArray['createTime']);
        $this->assertTrue($apiArray['isEnabled']);
    }
}
