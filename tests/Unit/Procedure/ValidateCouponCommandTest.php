<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Procedure\ValidateCouponCommand;
use Tourze\CouponCommandBundle\Service\CommandValidationService;

class ValidateCouponCommandTest extends TestCase
{
    private ValidateCouponCommand $procedure;
    /** @var MockObject&CommandValidationService */
    private MockObject $commandValidationService;

    protected function setUp(): void
    {
        $this->commandValidationService = $this->createMock(CommandValidationService::class);

        $this->procedure = new ValidateCouponCommand($this->commandValidationService);
    }

    public function test_execute_with_valid_command(): void
    {
        $command = 'VALID_CMD_2024';
        $userId = 'test_user_123';

        $expectedResult = [
            'valid' => true,
            'couponInfo' => [
                'id' => '1234567890',
                'name' => '测试优惠券',
                'type' => 'discount',
                'amount' => 100,
            ],
            'commandConfig' => [
                'id' => '9876543210',
                'command' => 'VALID_CMD_2024',
            ],
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('validateCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
    }

    public function test_execute_with_invalid_command(): void
    {
        $command = 'INVALID_CMD';
        $userId = 'test_user_123';

        $expectedResult = [
            'valid' => false,
            'reason' => '口令不存在',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('validateCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['valid']);
        $this->assertEquals('口令不存在', $result['reason']);
    }

    public function test_execute_with_user_restrictions(): void
    {
        $command = 'RESTRICTED_CMD';
        $userId = 'restricted_user';

        $expectedResult = [
            'valid' => false,
            'reason' => '您不在此口令的使用范围内',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('validateCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['valid']);
        $this->assertEquals('您不在此口令的使用范围内', $result['reason']);
    }

    public function test_execute_without_user_id(): void
    {
        $command = 'PUBLIC_CMD';

        $expectedResult = [
            'valid' => true,
            'couponInfo' => [
                'id' => '1111111111',
                'name' => '公开优惠券',
                'type' => 'discount',
                'amount' => 50,
            ],
            'commandConfig' => [
                'id' => '2222222222',
                'command' => 'PUBLIC_CMD',
            ],
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = null;

        $this->commandValidationService
            ->expects($this->once())
            ->method('validateCommand')
            ->with($command, null)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertTrue($result['valid']);
    }

    public function test_execute_with_time_limit_expired(): void
    {
        $command = 'EXPIRED_CMD';
        $userId = 'test_user';

        $expectedResult = [
            'valid' => false,
            'reason' => '口令使用时间超出有效期',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('validateCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['valid']);
        $this->assertEquals('口令使用时间超出有效期', $result['reason']);
    }

    public function test_execute_with_usage_limit_exceeded(): void
    {
        $command = 'LIMITED_CMD';
        $userId = 'test_user';

        $expectedResult = [
            'valid' => false,
            'reason' => '您已达到此口令的使用次数上限',
        ];

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->commandValidationService
            ->expects($this->once())
            ->method('validateCommand')
            ->with($command, $userId)
            ->willReturn($expectedResult);

        $result = $this->procedure->execute();

        $this->assertEquals($expectedResult, $result);
        $this->assertFalse($result['valid']);
        $this->assertEquals('您已达到此口令的使用次数上限', $result['reason']);
    }

    public function test_get_mock_result(): void
    {
        $mockResult = ValidateCouponCommand::getMockResult();

        $this->assertIsArray($mockResult);
        $this->assertArrayHasKey('valid', $mockResult);
        $this->assertArrayHasKey('couponInfo', $mockResult);
        $this->assertArrayHasKey('commandConfig', $mockResult);
        
        $this->assertTrue($mockResult['valid']);
        $this->assertNull($mockResult['reason']);
        
        // 验证 couponInfo 结构
        $this->assertIsArray($mockResult['couponInfo']);
        $this->assertArrayHasKey('id', $mockResult['couponInfo']);
        $this->assertArrayHasKey('name', $mockResult['couponInfo']);
        $this->assertArrayHasKey('type', $mockResult['couponInfo']);
        $this->assertArrayHasKey('amount', $mockResult['couponInfo']);
        
        // 验证 commandConfig 结构
        $this->assertIsArray($mockResult['commandConfig']);
        $this->assertArrayHasKey('id', $mockResult['commandConfig']);
        $this->assertArrayHasKey('command', $mockResult['commandConfig']);
        $this->assertArrayHasKey('commandLimit', $mockResult['commandConfig']);
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

    public function test_default_user_id_is_null(): void
    {
        $this->assertNull($this->procedure->userId);
    }
} 