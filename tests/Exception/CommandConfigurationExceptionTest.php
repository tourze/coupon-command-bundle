<?php

namespace Tourze\CouponCommandBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\CouponCommandBundle\Exception\CommandConfigurationException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CommandConfigurationException::class)]
final class CommandConfigurationExceptionTest extends AbstractExceptionTestCase
{
    protected function onSetUp(): void
    {
        // 单元测试设置，这里不需要特别的初始化
    }

    public function testExceptionInheritance(): void
    {
        $exception = new CommandConfigurationException('测试消息');
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(CommandConfigurationException::class, $exception);
    }

    public function testExceptionMessage(): void
    {
        $message = '口令配置异常测试';
        $exception = new CommandConfigurationException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $code = 1001;
        $exception = new CommandConfigurationException('测试', $code);
        $this->assertEquals($code, $exception->getCode());
    }
}
