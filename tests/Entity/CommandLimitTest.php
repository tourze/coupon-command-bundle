<?php

namespace Tourze\CouponCommandBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;

class CommandLimitTest extends TestCase
{
    private CommandLimit $commandLimit;

    protected function setUp(): void
    {
        $this->commandLimit = new CommandLimit();
    }

    public function test_command_limit_creation(): void
    {
        $this->assertInstanceOf(CommandLimit::class, $this->commandLimit);
        $this->assertNull($this->commandLimit->getId());
        $this->assertNull($this->commandLimit->getMaxUsagePerUser());
        $this->assertNull($this->commandLimit->getMaxTotalUsage());
        $this->assertEquals(0, $this->commandLimit->getCurrentUsage());
        $this->assertTrue($this->commandLimit->isEnabled());
    }

    public function test_usage_limits(): void
    {
        $this->commandLimit->setMaxUsagePerUser(5);
        $this->commandLimit->setMaxTotalUsage(100);
        $this->commandLimit->setCurrentUsage(10);

        $this->assertEquals(5, $this->commandLimit->getMaxUsagePerUser());
        $this->assertEquals(100, $this->commandLimit->getMaxTotalUsage());
        $this->assertEquals(10, $this->commandLimit->getCurrentUsage());
    }

    public function test_increment_usage(): void
    {
        $this->commandLimit->setCurrentUsage(5);
        $this->commandLimit->incrementUsage();

        $this->assertEquals(6, $this->commandLimit->getCurrentUsage());

        $this->commandLimit->incrementUsage();
        $this->assertEquals(7, $this->commandLimit->getCurrentUsage());
    }

    public function test_time_constraints(): void
    {
        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $endTime = new \DateTimeImmutable('2024-12-31 23:59:59');

        $this->commandLimit->setStartTime($startTime);
        $this->commandLimit->setEndTime($endTime);

        $this->assertEquals($startTime, $this->commandLimit->getStartTime());
        $this->assertEquals($endTime, $this->commandLimit->getEndTime());
    }

    public function test_user_restrictions(): void
    {
        $allowedUsers = ['user1', 'user2', 'user3'];
        $allowedUserTags = ['vip', 'premium'];

        $this->commandLimit->setAllowedUsers($allowedUsers);
        $this->commandLimit->setAllowedUserTags($allowedUserTags);

        $this->assertEquals($allowedUsers, $this->commandLimit->getAllowedUsers());
        $this->assertEquals($allowedUserTags, $this->commandLimit->getAllowedUserTags());
    }

    public function test_enabled_status(): void
    {
        $this->assertTrue($this->commandLimit->isEnabled());

        $this->commandLimit->setIsEnabled(false);
        $this->assertFalse($this->commandLimit->isEnabled());

        $this->commandLimit->setIsEnabled(true);
        $this->assertTrue($this->commandLimit->isEnabled());
    }

    public function test_is_time_valid_with_no_constraints(): void
    {
        // 没有时间限制，应该总是有效
        $this->assertTrue($this->commandLimit->isTimeValid());
    }

    public function test_is_time_valid_with_start_time_only(): void
    {
        // 只设置开始时间
        $pastTime = new \DateTimeImmutable('-1 hour');
        $futureTime = new \DateTimeImmutable('+1 hour');

        $this->commandLimit->setStartTime($pastTime);
        $this->assertTrue($this->commandLimit->isTimeValid());

        $this->commandLimit->setStartTime($futureTime);
        $this->assertFalse($this->commandLimit->isTimeValid());
    }

    public function test_is_time_valid_with_end_time_only(): void
    {
        // 只设置结束时间
        $pastTime = new \DateTimeImmutable('-1 hour');
        $futureTime = new \DateTimeImmutable('+1 hour');

        $this->commandLimit->setEndTime($futureTime);
        $this->assertTrue($this->commandLimit->isTimeValid());

        $this->commandLimit->setEndTime($pastTime);
        $this->assertFalse($this->commandLimit->isTimeValid());
    }

    public function test_is_time_valid_with_both_constraints(): void
    {
        // 设置时间窗口
        $startTime = new \DateTimeImmutable('-1 hour');
        $endTime = new \DateTimeImmutable('+1 hour');

        $this->commandLimit->setStartTime($startTime);
        $this->commandLimit->setEndTime($endTime);
        $this->assertTrue($this->commandLimit->isTimeValid());

        // 过期的时间窗口
        $expiredStart = new \DateTimeImmutable('-2 hours');
        $expiredEnd = new \DateTimeImmutable('-1 hour');

        $this->commandLimit->setStartTime($expiredStart);
        $this->commandLimit->setEndTime($expiredEnd);
        $this->assertFalse($this->commandLimit->isTimeValid());
    }

    public function test_has_total_usage_quota_with_no_limit(): void
    {
        // 没有总量限制
        $this->commandLimit->setMaxTotalUsage(null);
        $this->commandLimit->setCurrentUsage(999999);
        $this->assertTrue($this->commandLimit->hasTotalUsageQuota());
    }

    public function test_has_total_usage_quota_with_limit(): void
    {
        $this->commandLimit->setMaxTotalUsage(100);

        // 未达到限制
        $this->commandLimit->setCurrentUsage(50);
        $this->assertTrue($this->commandLimit->hasTotalUsageQuota());

        // 达到限制
        $this->commandLimit->setCurrentUsage(100);
        $this->assertFalse($this->commandLimit->hasTotalUsageQuota());

        // 超过限制
        $this->commandLimit->setCurrentUsage(150);
        $this->assertFalse($this->commandLimit->hasTotalUsageQuota());
    }

    public function test_is_user_allowed_with_no_restrictions(): void
    {
        // 没有用户限制
        $this->commandLimit->setAllowedUsers(null);
        $this->assertTrue($this->commandLimit->isUserAllowed('any_user'));
    }

    public function test_is_user_allowed_with_whitelist(): void
    {
        $allowedUsers = ['user1', 'user2', 'user3'];
        $this->commandLimit->setAllowedUsers($allowedUsers);

        $this->assertTrue($this->commandLimit->isUserAllowed('user1'));
        $this->assertTrue($this->commandLimit->isUserAllowed('user2'));
        $this->assertFalse($this->commandLimit->isUserAllowed('user4'));
    }

    public function test_command_config_relationship(): void
    {
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_LIMIT');

        $this->commandLimit->setCommandConfig($commandConfig);

        $this->assertSame($commandConfig, $this->commandLimit->getCommandConfig());
    }

    public function test_retrieve_api_array(): void
    {
        $this->commandLimit->setMaxUsagePerUser(5);
        $this->commandLimit->setMaxTotalUsage(100);
        $this->commandLimit->setCurrentUsage(25);
        $this->commandLimit->setAllowedUsers(['user1', 'user2']);
        $this->commandLimit->setAllowedUserTags(['vip']);

        $startTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $createTime = new \DateTimeImmutable('2024-01-01 09:00:00');

        $this->commandLimit->setStartTime($startTime);
        $this->commandLimit->setCreateTime($createTime);

        $apiArray = $this->commandLimit->retrieveApiArray();
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
