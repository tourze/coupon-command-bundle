<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tourze\CouponCommandBundle\Procedure\UseCouponCommand;
use Tourze\CouponCommandBundle\Service\CommandValidationService;

class UseCouponCommandTest extends TestCase
{
    private UseCouponCommand $procedure;
    /** @var MockObject&ContainerInterface */
    private MockObject $container;
    /** @var MockObject&CommandValidationService */
    private MockObject $validationService;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->validationService = $this->createMock(CommandValidationService::class);
        
        $this->procedure = new UseCouponCommand();
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

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

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

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

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

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

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

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

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

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

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

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

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
        $command = 'TEST_PROP_CMD';
        $userId = 'prop_user_123';

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->assertEquals($command, $this->procedure->command);
        $this->assertEquals($userId, $this->procedure->userId);
    }

    public function test_required_properties_validation(): void
    {
        // 验证 command 和 userId 都是必需的
        $this->procedure->command = 'REQUIRED_TEST';
        $this->procedure->userId = 'required_user';

        $this->assertIsString($this->procedure->command);
        $this->assertIsString($this->procedure->userId);
        
        // 确保属性不为空
        $this->assertNotEmpty($this->procedure->command);
        $this->assertNotEmpty($this->procedure->userId);
    }

    public function test_successful_coupon_acquisition_flow(): void
    {
        $command = 'FLOW_TEST_CMD';
        $userId = 'flow_test_user';
        $couponId = 'flow_coupon_999';

        $expectedResult = [
            'success' => true,
            'couponId' => $couponId,
            'message' => '优惠券领取成功',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        // 验证成功流程的各个方面
        $this->assertTrue($result['success']);
        $this->assertEquals($couponId, $result['couponId']);
        $this->assertStringContainsString('成功', $result['message']);
        $this->assertArrayNotHasKey('reason', $result); // 成功时不应有 reason 字段
    }

    public function test_failed_coupon_acquisition_flow(): void
    {
        $command = 'FAIL_FLOW_CMD';
        $userId = 'fail_flow_user';

        $expectedResult = [
            'success' => false,
            'message' => '优惠券库存不足',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('Tourze\\CouponCommandBundle\\Procedure\\UseCouponCommand::getCommandValidationService')
            ->willReturn($this->validationService);

        $this->validationService
            ->expects($this->once())
            ->method('useCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        // 验证失败流程的各个方面
        $this->assertFalse($result['success']);
        $this->assertArrayNotHasKey('couponId', $result); // 失败时不应有 couponId
        $this->assertIsString($result['message']);
        $this->assertNotEmpty($result['message']);
    }
} 