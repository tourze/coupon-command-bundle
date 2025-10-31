<?php

namespace Tourze\CouponCommandBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Exception\CommandConfigurationException;
use Tourze\CouponCommandBundle\Service\CommandManagementService;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CommandManagementService::class)]
#[RunTestsInSeparateProcesses]
final class CommandManagementServiceTest extends AbstractIntegrationTestCase
{
    private CommandManagementService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(CommandManagementService::class);
    }

    public function testServiceInitialization(): void
    {
        $this->assertInstanceOf(CommandManagementService::class, $this->service);
    }

    public function testServiceHasRequiredMethods(): void
    {
        $reflection = new \ReflectionClass($this->service);

        $this->assertTrue($reflection->hasMethod('createCommandConfig'));
        $this->assertTrue($reflection->hasMethod('updateCommandConfig'));
        $this->assertTrue($reflection->hasMethod('deleteCommandConfig'));
        $this->assertTrue($reflection->hasMethod('addCommandLimit'));
        $this->assertTrue($reflection->hasMethod('updateCommandLimit'));
        $this->assertTrue($reflection->hasMethod('deleteCommandLimit'));
        $this->assertTrue($reflection->hasMethod('getCommandConfigDetail'));
        $this->assertTrue($reflection->hasMethod('getCommandConfigList'));
        $this->assertTrue($reflection->hasMethod('toggleCommandLimitStatus'));
    }

    public function testCreateCommandConfig(): void
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

        $result = $this->service->createCommandConfig('TESTCOMMAND123', $coupon);

        $this->assertInstanceOf(CommandConfig::class, $result);
        $this->assertSame('TESTCOMMAND123', $result->getCommand());
        $this->assertSame($coupon, $result->getCoupon());
        $this->assertNotNull($result->getId());
    }

    public function testCreateCommandConfigWithDuplicateCommand(): void
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

        $this->service->createCommandConfig('DUPLICATE123', $coupon);

        $this->expectException(CommandConfigurationException::class);
        $this->expectExceptionMessage('口令已存在');

        $this->service->createCommandConfig('DUPLICATE123', $coupon);
    }

    public function testUpdateCommandConfig(): void
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

        $commandConfig = $this->service->createCommandConfig('ORIGINAL123', $coupon);
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);

        $updatedConfig = $this->service->updateCommandConfig($configId, 'UPDATED123');

        $this->assertSame('UPDATED123', $updatedConfig->getCommand());
        $this->assertSame($configId, $updatedConfig->getId());
    }

    public function testDeleteCommandConfig(): void
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

        $commandConfig = $this->service->createCommandConfig('DELETE123', $coupon);
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);

        $result = $this->service->deleteCommandConfig($configId);
        $this->assertTrue($result);

        $result = $this->service->deleteCommandConfig($configId);
        $this->assertFalse($result);
    }

    public function testAddCommandLimit(): void
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

        $commandConfig = $this->service->createCommandConfig('LIMITTEST123', $coupon);
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);

        $startTime = new \DateTime('2025-01-01');
        $endTime = new \DateTime('2025-12-31');
        $allowedUsers = ['user1', 'user2'];

        $commandLimit = $this->service->addCommandLimit(
            $configId,
            1,
            100,
            $startTime,
            $endTime,
            $allowedUsers
        );

        $this->assertInstanceOf(CommandLimit::class, $commandLimit);
        $this->assertSame(1, $commandLimit->getMaxUsagePerUser());
        $this->assertSame(100, $commandLimit->getMaxTotalUsage());
        $this->assertEquals($startTime->format('Y-m-d'), $commandLimit->getStartTime()?->format('Y-m-d'));
        $this->assertEquals($endTime->format('Y-m-d'), $commandLimit->getEndTime()?->format('Y-m-d'));
        $this->assertSame($allowedUsers, $commandLimit->getAllowedUsers());
    }

    public function testUpdateCommandLimit(): void
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

        $commandConfig = $this->service->createCommandConfig('UPDATETEST123', $coupon);
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);

        $commandLimit = $this->service->addCommandLimit($configId, 1, 100);
        $limitId = $commandLimit->getId();
        $this->assertNotNull($limitId);

        $updatedLimit = $this->service->updateCommandLimit(
            $limitId,
            2,
            200,
            null,
            null,
            null,
            null,
            false
        );

        $this->assertSame(2, $updatedLimit->getMaxUsagePerUser());
        $this->assertSame(200, $updatedLimit->getMaxTotalUsage());
        $this->assertFalse($updatedLimit->isEnabled());
    }

    public function testDeleteCommandLimit(): void
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

        $commandConfig = $this->service->createCommandConfig('DELETELIIMIT123', $coupon);
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);

        $commandLimit = $this->service->addCommandLimit($configId, 1, 100);
        $limitId = $commandLimit->getId();
        $this->assertNotNull($limitId);

        $result = $this->service->deleteCommandLimit($limitId);
        $this->assertTrue($result);

        $result = $this->service->deleteCommandLimit($limitId);
        $this->assertFalse($result);
    }

    public function testToggleCommandLimitStatus(): void
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

        $commandConfig = $this->service->createCommandConfig('TOGGLETEST123', $coupon);
        $configId = $commandConfig->getId();
        $this->assertNotNull($configId);

        $commandLimit = $this->service->addCommandLimit($configId, 1, 100);
        $limitId = $commandLimit->getId();
        $this->assertNotNull($limitId);

        $this->assertTrue($commandLimit->isEnabled());

        $toggledLimit = $this->service->toggleCommandLimitStatus($limitId);
        $this->assertFalse($toggledLimit->isEnabled());

        $toggledAgain = $this->service->toggleCommandLimitStatus($limitId);
        $this->assertTrue($toggledAgain->isEnabled());
    }
}
