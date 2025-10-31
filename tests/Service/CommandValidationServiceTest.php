<?php

namespace Tourze\CouponCommandBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Service\CommandValidationService;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CommandValidationService::class)]
#[RunTestsInSeparateProcesses]
final class CommandValidationServiceTest extends AbstractIntegrationTestCase
{
    private CommandValidationService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(CommandValidationService::class);
    }

    public function testServiceInitialization(): void
    {
        $this->assertInstanceOf(CommandValidationService::class, $this->service);
    }

    public function testServiceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass($this->service);

        $this->assertTrue($reflection->hasMethod('validateCommand'));
        $this->assertTrue($reflection->hasMethod('useCommand'));
        $this->assertTrue($reflection->hasMethod('getUserUsageRecords'));
        $this->assertTrue($reflection->hasMethod('getCommandUsageRecords'));
    }

    public function testValidateCommand(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setExpireDay(30);
        $coupon->setIconImg('test.jpg');
        $coupon->setBackImg('test-back.jpg');
        $coupon->setRemark('Test coupon for testing');
        $coupon->setStartDateTime(new \DateTime('2025-01-01'));
        $coupon->setEndDateTime(new \DateTime('2025-12-31'));
        $coupon->setNeedActive(false);
        $coupon->setUseDesc('Test description');
        $coupon->setStartTime(new \DateTime('2025-01-01'));
        $coupon->setEndTime(new \DateTime('2025-12-31'));
        $coupon->setValid(true);

        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCoupon($coupon);
        $commandConfig->setCommand('VALIDTEST123');

        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $result = $this->service->validateCommand('VALIDTEST123');

        $this->assertTrue($result['valid']);
        $this->assertArrayHasKey('couponInfo', $result);
        $this->assertArrayHasKey('commandConfig', $result);
    }

    public function testValidateCommandNotExists(): void
    {
        $result = $this->service->validateCommand('NONEXISTENT123');

        $this->assertFalse($result['valid']);
        $this->assertSame('口令不存在', $result['reason']);
    }

    public function testValidateCommandWithLimits(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setExpireDay(30);
        $coupon->setIconImg('test.jpg');
        $coupon->setBackImg('test-back.jpg');
        $coupon->setRemark('Test coupon for testing');
        $coupon->setStartDateTime(new \DateTime('2025-01-01'));
        $coupon->setEndDateTime(new \DateTime('2025-12-31'));
        $coupon->setNeedActive(false);
        $coupon->setUseDesc('Test description');
        $coupon->setStartTime(new \DateTime('2025-01-01'));
        $coupon->setEndTime(new \DateTime('2025-12-31'));
        $coupon->setValid(true);

        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCoupon($coupon);
        $commandConfig->setCommand('LIMITEDTEST123');

        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxTotalUsage(10);
        $commandLimit->setCurrentUsage(0);
        $commandLimit->setIsEnabled(true);
        $commandLimit->setStartTime(new \DateTimeImmutable('2025-01-01'));
        $commandLimit->setEndTime(new \DateTimeImmutable('2025-12-31'));

        self::getEntityManager()->persist($commandLimit);
        self::getEntityManager()->flush();

        $result = $this->service->validateCommand('LIMITEDTEST123', 'user123');

        $this->assertTrue($result['valid']);
        $this->assertArrayHasKey('couponInfo', $result);
    }

    public function testUseCommand(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setExpireDay(30);
        $coupon->setIconImg('test.jpg');
        $coupon->setBackImg('test-back.jpg');
        $coupon->setRemark('Test coupon for testing');
        $coupon->setStartDateTime(new \DateTime('2025-01-01'));
        $coupon->setEndDateTime(new \DateTime('2025-12-31'));
        $coupon->setNeedActive(false);
        $coupon->setUseDesc('Test description');
        $coupon->setStartTime(new \DateTime('2025-01-01'));
        $coupon->setEndTime(new \DateTime('2025-12-31'));
        $coupon->setValid(true);

        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCoupon($coupon);
        $commandConfig->setCommand('USETEST123');

        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $result = $this->service->useCommand('USETEST123', 'user123');

        $this->assertTrue($result['success']);
        $this->assertSame('优惠券领取成功', $result['message']);
        $this->assertArrayHasKey('couponId', $result);
    }

    public function testUseCommandInvalid(): void
    {
        $result = $this->service->useCommand('INVALID123', 'user123');

        $this->assertFalse($result['success']);
        $this->assertSame('口令不存在', $result['message']);
    }

    public function testUseCommandWithLimitExceeded(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Test Coupon');
        $coupon->setExpireDay(30);
        $coupon->setIconImg('test.jpg');
        $coupon->setBackImg('test-back.jpg');
        $coupon->setRemark('Test coupon for testing');
        $coupon->setStartDateTime(new \DateTime('2025-01-01'));
        $coupon->setEndDateTime(new \DateTime('2025-12-31'));
        $coupon->setNeedActive(false);
        $coupon->setUseDesc('Test description');
        $coupon->setStartTime(new \DateTime('2025-01-01'));
        $coupon->setEndTime(new \DateTime('2025-12-31'));
        $coupon->setValid(true);

        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCoupon($coupon);
        $commandConfig->setCommand('LIMITEXCEEDED123');

        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $commandLimit = new CommandLimit();
        $commandLimit->setMaxTotalUsage(1);
        $commandLimit->setCurrentUsage(1);
        $commandLimit->setIsEnabled(true);

        $commandConfig->setCommandLimit($commandLimit);

        self::getEntityManager()->persist($commandLimit);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $result = $this->service->useCommand('LIMITEXCEEDED123', 'user123');

        $this->assertFalse($result['success']);
        $this->assertSame('口令使用次数已达上限', $result['message']);
    }
}
