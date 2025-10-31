<?php

namespace Tourze\CouponCommandBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\CouponCommandBundle\Controller\Admin\CommandLimitCrudController;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(CommandLimitCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CommandLimitCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return CommandLimitCrudController
     */
    protected function getControllerService(): CommandLimitCrudController
    {
        /** @var CommandLimitCrudController $controller */
        $controller = self::getContainer()->get(CommandLimitCrudController::class);
        self::assertInstanceOf(CommandLimitCrudController::class, $controller);

        return $controller;
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID 列' => ['ID'];
        yield '每人限领次数列' => ['每人限领次数'];
        yield '总限领次数列' => ['总限领次数'];
        yield '当前已使用次数列' => ['当前已使用次数'];
        yield '开始有效时间列' => ['开始有效时间'];
        yield '结束有效时间列' => ['结束有效时间'];
        yield '是否启用列' => ['是否启用'];
        yield '创建时间列' => ['创建时间'];
        yield '更新时间列' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '每人限领次数字段' => ['maxUsagePerUser'];
        yield '总限领次数字段' => ['maxTotalUsage'];
        yield '开始有效时间字段' => ['startTime'];
        yield '结束有效时间字段' => ['endTime'];
        yield '是否启用字段' => ['isEnabled'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield '每人限领次数字段' => ['maxUsagePerUser'];
        yield '总限领次数字段' => ['maxTotalUsage'];
        yield '开始有效时间字段' => ['startTime'];
        yield '结束有效时间字段' => ['endTime'];
        yield '是否启用字段' => ['isEnabled'];
    }

    public function testIndexPageRequiresAuthentication(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/coupon-command/limit/');
    }

    public function testNewPageRequiresAuthentication(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/coupon-command/limit/new');
    }

    public function testCommandConfigFieldValidation(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();

        $client->request('POST', '/admin/coupon-command/limit/new', [
            'CommandLimit' => [
                'commandConfig' => '',
                'maxUsagePerUser' => '5',
                'isEnabled' => '1',
            ],
        ]);
    }

    public function testIsEnabledFilterSearch(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/coupon-command/limit/', ['filters[isEnabled][value]' => '1']);
    }

    public function testMaxUsagePerUserFilterSearch(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/coupon-command/limit/', ['filters[maxUsagePerUser][value]' => '5']);
    }

    public function testMaxTotalUsageFilterSearch(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/coupon-command/limit/', ['filters[maxTotalUsage][value]' => '100']);
    }

    public function testCurrentUsageFilterSearch(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();
        $client->request('GET', '/admin/coupon-command/limit/', ['filters[currentUsage][value]' => '10']);
    }

    public function testValidationErrorsWithEmptyCommandConfigField(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();

        $client->request('POST', '/admin/coupon-command/limit/new', [
            'CommandLimit' => [
                'commandConfig' => '',
                'maxUsagePerUser' => '5',
                'isEnabled' => true,
            ],
        ]);
    }

    public function testValidationWithRequiredFieldsPresent(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();

        $client->request('POST', '/admin/coupon-command/limit/new', [
            'CommandLimit' => [
                'commandConfig' => '123',
                'maxUsagePerUser' => '10',
                'maxTotalUsage' => '100',
                'isEnabled' => true,
            ],
        ]);
    }

    public function testValidationWithNegativeUsageValues(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();

        $client->request('POST', '/admin/coupon-command/limit/new', [
            'CommandLimit' => [
                'commandConfig' => '123',
                'maxUsagePerUser' => '-1',
                'maxTotalUsage' => '-5',
                'isEnabled' => true,
            ],
        ]);
    }

    public function testFormStructureValidation(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::createClientWithDatabase();

        $client->request('POST', '/admin/coupon-command/limit/new', [
            'InvalidForm' => [
                'invalid_field' => 'value',
            ],
        ]);
    }

    public function testControllerRouteAccessibility(): void
    {
        $client = self::createClientWithDatabase();

        $routes = [
            '/admin/coupon-command/limit/',
            '/admin/coupon-command/limit/new',
        ];

        foreach ($routes as $route) {
            $this->expectException(AccessDeniedException::class);
            $client->request('GET', $route);
        }
    }
}
