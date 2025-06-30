<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Exception\CommandConfigurationException;

class CommandConfigurationExceptionTest extends TestCase
{
    public function test_exception_inheritance(): void
    {
        $exception = new CommandConfigurationException('测试消息');
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(CommandConfigurationException::class, $exception);
    }

    public function test_exception_message(): void
    {
        $message = '口令配置异常测试';
        $exception = new CommandConfigurationException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_exception_code(): void
    {
        $code = 1001;
        $exception = new CommandConfigurationException('测试', $code);
        $this->assertEquals($code, $exception->getCode());
    }
}