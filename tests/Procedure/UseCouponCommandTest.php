<?php

namespace Tourze\CouponCommandBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Constraints\Collection;
use Tourze\CouponCommandBundle\Procedure\UseCouponCommand;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(UseCouponCommand::class)]
#[RunTestsInSeparateProcesses]
final class UseCouponCommandTest extends AbstractProcedureTestCase
{
    private UseCouponCommand $procedure;

    protected function onSetUp(): void
    {
        $procedure = self::getContainer()->get(UseCouponCommand::class);
        $this->assertInstanceOf(UseCouponCommand::class, $procedure);
        $this->procedure = $procedure;
    }

    public function testPropertyAssignments(): void
    {
        $command = 'TEST_COMMAND';
        $userId = 'user123';

        $this->procedure->command = $command;
        $this->procedure->userId = $userId;

        $this->assertEquals($command, $this->procedure->command);
        $this->assertEquals($userId, $this->procedure->userId);
    }

    public function testGetMockResult(): void
    {
        $mockResult = UseCouponCommand::getMockResult();
        $this->assertNotNull($mockResult);
        $this->assertArrayHasKey('success', $mockResult);
        $this->assertArrayHasKey('couponId', $mockResult);
        $this->assertArrayHasKey('message', $mockResult);
        $this->assertTrue($mockResult['success']);
        $this->assertEquals('1234567890', $mockResult['couponId']);
        $this->assertEquals('优惠券领取成功', $mockResult['message']);
    }

    public function testExecute(): void
    {
        $this->procedure->command = 'TEST_COMMAND';
        $this->procedure->userId = 'user123';

        $result = $this->procedure->execute();

        // 验证返回结果的基本结构
        $this->assertArrayHasKey('success', $result);
        $this->assertIsBool($result['success']);
    }

    public function testAssignParams(): void
    {
        $params = [
            'command' => 'TEST_COMMAND',
            'userId' => 'user123',
        ];

        $this->procedure->assignParams($params);

        $this->assertEquals('TEST_COMMAND', $this->procedure->command);
        $this->assertEquals('user123', $this->procedure->userId);
        $this->assertEquals($params, $this->procedure->paramList);
    }

    public function testAssignParamsWithNull(): void
    {
        $this->procedure->assignParams(null);

        $this->assertNull($this->procedure->paramList);
    }

    public function testGetParamsConstraint(): void
    {
        $constraint = $this->procedure->getParamsConstraint();

        $this->assertInstanceOf(Collection::class, $constraint);

        $fields = $constraint->fields;
        $this->assertArrayHasKey('command', $fields);
        $this->assertArrayHasKey('userId', $fields);
    }

    public function testGetPropertyDocument(): void
    {
        $commandDoc = $this->procedure->getPropertyDocument('command');
        $this->assertNotNull($commandDoc);
        $this->assertArrayHasKey('name', $commandDoc);
        $this->assertArrayHasKey('type', $commandDoc);
        $this->assertArrayHasKey('description', $commandDoc);
        $this->assertEquals('command', $commandDoc['name']);
        $this->assertEquals('string', $commandDoc['type']);
        $this->assertEquals('口令内容', $commandDoc['description']);

        $userIdDoc = $this->procedure->getPropertyDocument('userId');
        $this->assertNotNull($userIdDoc);
        $this->assertArrayHasKey('name', $userIdDoc);
        $this->assertArrayHasKey('type', $userIdDoc);
        $this->assertArrayHasKey('description', $userIdDoc);
        $this->assertEquals('userId', $userIdDoc['name']);
        $this->assertEquals('string', $userIdDoc['type']);
        $this->assertEquals('用户ID', $userIdDoc['description']);
    }

    public function testGetSubscribedServices(): void
    {
        $services = $this->procedure::getSubscribedServices();
        $this->assertNotEmpty($services);
        foreach (array_keys($services) as $key) {
            $this->assertIsString($key, 'Service key should be a string');
        }
    }
}
