<?php

namespace Tourze\CouponCommandBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\CouponCommandBundle\Controller\Admin\CommandUsageRecordCrudController;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(CommandUsageRecordCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CommandUsageRecordCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CommandUsageRecordCrudController
    {
        $controller = self::getContainer()->get(CommandUsageRecordCrudController::class);
        self::assertInstanceOf(CommandUsageRecordCrudController::class, $controller);

        return $controller;
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '口令配置' => ['口令配置'];
        yield '用户ID' => ['用户ID'];
        yield '使用的口令' => ['使用的口令'];
        yield '获得的优惠券ID' => ['获得的优惠券ID'];
        yield '是否成功' => ['是否成功'];
        yield '创建人' => ['创建人'];
        yield '使用时间' => ['使用时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'userId' => ['userId'];
        yield 'commandText' => ['commandText'];
        yield 'couponId' => ['couponId'];
        yield 'isSuccess' => ['isSuccess'];
        yield 'failureReason' => ['failureReason'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'userId' => ['userId'];
        yield 'commandText' => ['commandText'];
        yield 'couponId' => ['couponId'];
        yield 'isSuccess' => ['isSuccess'];
        yield 'failureReason' => ['failureReason'];
    }

    public function testIndexPageRequiresAuthentication(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/');
    }

    public function testDetailPageRequiresAuthentication(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/1');
    }

    public function testCreateAndEditActionsAreDisabled(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/new');
    }

    public function testUserIdFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/', ['filters[userId][value]' => 'user123']);
    }

    public function testCommandTextFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/', ['filters[commandText][value]' => 'test_command']);
    }

    public function testCouponIdFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/', ['filters[couponId][value]' => 'coupon123']);
    }

    public function testIsSuccessFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/', ['filters[isSuccess][value]' => '1']);
    }

    public function testCreateTimeFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/coupon-command/usage-record/', ['filters[createTime][value]' => '2023-01-01']);
    }
}
