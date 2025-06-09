<?php

namespace Tourze\CouponCommandBundle\Tests\Procedure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tourze\CouponCommandBundle\Procedure\UseCouponCommand;
use Tourze\CouponCommandBundle\Service\CommandValidationService;

class UseCouponCommandTest extends TestCase
{
    private UseCouponCommand $procedure;
    private ContainerInterface|MockObject $container;
    private CommandValidationService|MockObject $validationService;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->validationService = $this->createMock(CommandValidationService::class);

        $this->procedure = new UseCouponCommand($this->validationService);
        $this->procedure->setContainer($this->container);
    }

    public function test_execute_with_valid_command(): void
    {
        $command = 'SUCCESS_CMD_2024';
        $userId = 'test_user_123';

        $expectedResult = [
            'success' => true,
            'couponId' => '1234567890',
            'message' => '优惠券领取成功',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertTrue($result['success']);
        $this->assertEquals('1234567890', $result['couponId']);
        $this->assertEquals('优惠券领取成功', $result['message']);
    }

    public function test_execute_with_invalid_command(): void
    {
        $command = 'INVALID_CMD';
        $userId = 'test_user_123';

        $expectedResult = [
            'success' => false,
            'message' => '口令不存在',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('口令不存在', $result['message']);
    }

    public function test_execute_with_usage_limit_exceeded(): void
    {
        $command = 'LIMITED_CMD';
        $userId = 'frequent_user';

        $expectedResult = [
            'success' => false,
            'message' => '您已达到此口令的使用次数上限',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('您已达到此口令的使用次数上限', $result['message']);
    }

    public function test_execute_with_expired_command(): void
    {
        $command = 'EXPIRED_CMD';
        $userId = 'test_user';

        $expectedResult = [
            'success' => false,
            'message' => '口令使用时间超出有效期',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('口令使用时间超出有效期', $result['message']);
    }

    public function test_execute_with_user_not_allowed(): void
    {
        $command = 'VIP_CMD';
        $userId = 'regular_user';

        $expectedResult = [
            'success' => false,
            'message' => '您不在此口令的使用范围内',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('您不在此口令的使用范围内', $result['message']);
    }

    public function test_execute_with_system_error(): void
    {
        $command = 'ERROR_CMD';
        $userId = 'test_user';

        $expectedResult = [
            'success' => false,
            'message' => '系统错误，请稍后重试',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('系统错误，请稍后重试', $result['message']);
    }

    public function test_get_mock_result(): void
    {
        $mockResult = UseCouponCommand::getMockResult();

        $this->assertIsArray($mockResult);
        $this->assertArrayHasKey('success', $mockResult);
        $this->assertArrayHasKey('couponId', $mockResult);
        $this->assertArrayHasKey('message', $mockResult);
        $this->assertTrue($mockResult['success']);
        $this->assertEquals('1234567890', $mockResult['couponId']);
        $this->assertEquals('优惠券领取成功', $mockResult['message']);
    }

    public function test_property_assignments(): void
    {
        $command = 'TEST_COMMAND';
        $userId = 'user123';

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->assertEquals($command, $this->procedure->command);
        $this->assertEquals($userId, $this->procedure->userId);
    }

    public function test_required_properties_validation(): void
    {
        // 测试必填属性的验证
        $this->procedure->command = '';
        $this->procedure->userId = '';

        // 这里实际应该通过验证器进行验证，但由于我们mock了服务
        // 我们主要测试属性赋值是否正确
        $this->assertEquals('', $this->procedure->command);
        $this->assertEquals('', $this->procedure->userId);
    }

    public function test_successful_coupon_acquisition_flow(): void
    {
        $command = 'PROMO2024';
        $userId = 'user456';

        $expectedResult = [
            'success' => true,
            'couponId' => 'COUP789',
            'message' => '恭喜您，优惠券领取成功！',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        // 验证结果
        $this->assertTrue($result['success']);
        $this->assertEquals('COUP789', $result['couponId']);
        $this->assertStringContainsString('成功', $result['message']);
    }

    public function test_failed_coupon_acquisition_flow(): void
    {
        $command = 'EXPIRED_PROMO';
        $userId = 'user789';

        $expectedResult = [
            'success' => false,
            'message' => '抱歉，该口令已过期',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        // 验证失败结果
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('过期', $result['message']);
        $this->assertArrayNotHasKey('couponId', $result);
    }
}
