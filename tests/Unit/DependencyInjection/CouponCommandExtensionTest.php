<?php

namespace Tourze\CouponCommandBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CouponCommandBundle\DependencyInjection\CouponCommandExtension;

class CouponCommandExtensionTest extends TestCase
{
    private CouponCommandExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new CouponCommandExtension();
        $this->container = new ContainerBuilder();
    }

    public function test_extension_initialization(): void
    {
        $this->assertInstanceOf(CouponCommandExtension::class, $this->extension);
    }

    public function test_load_with_empty_config(): void
    {
        $configs = [];
        
        $this->extension->load($configs, $this->container);
        
        // 验证加载成功
        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }

    public function test_extension_alias(): void
    {
        $alias = $this->extension->getAlias();
        $this->assertEquals('coupon_command', $alias);
    }
}