<?php

namespace Tourze\CouponCommandBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CouponCommandBundle\CouponCommandBundle;
use Tourze\CouponCommandBundle\DependencyInjection\CouponCommandExtension;

class CouponCommandBundleTest extends TestCase
{
    private CouponCommandBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new CouponCommandBundle();
    }

    public function test_bundle_initialization(): void
    {
        $this->assertInstanceOf(CouponCommandBundle::class, $this->bundle);
    }

    public function test_bundle_has_extension(): void
    {
        $extension = $this->bundle->getContainerExtension();
        $this->assertInstanceOf(CouponCommandExtension::class, $extension);
    }

    public function test_bundle_build(): void
    {
        $container = new ContainerBuilder();
        $this->bundle->build($container);
        
        // 验证容器构建没有抛出异常
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}