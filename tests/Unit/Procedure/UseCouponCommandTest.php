<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Procedure\UseCouponCommand;
use Tourze\CouponCommandBundle\Service\CommandValidationService;

class UseCouponCommandTest extends TestCase
{
    private UseCouponCommand $procedure;
    private CommandValidationService|MockObject $commandValidationService;

    protected function setUp(): void
    {
        $this->commandValidationService = $this->createMock(CommandValidationService::class);

        $this->procedure = new UseCouponCommand($this->commandValidationService);
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

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertTrue($result['success']);
        $this->assertEquals('1234567890', $result['couponId']);
        $this->assertEquals('优惠券领取成功', $result['message']);
        $this->assertEquals($userId, $this->procedure->userId);
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

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('口令不存在', $result['message']);
        $this->assertEquals($userId, $this->procedure->userId);
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

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('您已达到此口令的使用次数上限', $result['message']);
        $this->assertEquals($userId, $this->procedure->userId);
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

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('口令使用时间超出有效期', $result['message']);
        $this->assertEquals($userId, $this->procedure->userId);
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

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('您不在此口令的使用范围内', $result['message']);
        $this->assertEquals($userId, $this->procedure->userId);
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

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('系统错误，请稍后重试', $result['message']);
        $this->assertEquals($userId, $this->procedure->userId);
    }

    public function test_get_mock_result(): void
    {
        $expected = [
            'success' => true,
            'couponId' => '1234567890',
            'message' => '优惠券领取成功',
        ];

        $this->assertEquals($expected, UseCouponCommand::getMockResult());
    }

    public function test_property_assignments(): void
    {
        $command = 'TEST_CMD';
        $userId = 'test_user';

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->assertEquals($command, $this->procedure->command);
        $this->assertEquals($userId, $this->procedure->userId);
    }


    public function test_successful_coupon_acquisition_flow(): void
    {
        $command = 'SUCCESS_FLOW_CMD';
        $userId = 'flow_user';

        $expectedResult = [
            'success' => true,
            'couponId' => 'flow_coupon_123',
            'message' => '领取成功',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();
        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($userId, $this->procedure->userId);
    }

    public function test_failed_coupon_acquisition_flow(): void
    {
        $command = 'FAIL_FLOW_CMD';
        $userId = 'flow_user';

        $expectedResult = [
            'success' => false,
            'message' => '领取失败',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();
        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($userId, $this->procedure->userId);
    }
}
