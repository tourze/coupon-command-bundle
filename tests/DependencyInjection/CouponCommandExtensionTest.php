<?php

namespace Tourze\CouponCommandBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\CouponCommandBundle\DependencyInjection\CouponCommandExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(CouponCommandExtension::class)]
final class CouponCommandExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private CouponCommandExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new CouponCommandExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testLoadDoesNotThrowException(): void
    {
        // 我们只测试方法不会抛出异常
        $configs = [];

        $this->expectNotToPerformAssertions();
        $this->extension->load($configs, $this->container);
    }

    public function testExtensionAlias(): void
    {
        $this->assertEquals('coupon_command', $this->extension->getAlias());
    }

    public function testLoadWithEmptyConfigs(): void
    {
        // 测试空配置数组
        $emptyConfigs = [];

        $this->expectNotToPerformAssertions();
        $this->extension->load($emptyConfigs, $this->container);
    }

    public function testContainerAfterLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证容器中是否有自动配置的服务
        $definitions = $this->container->getDefinitions();
        $this->assertNotEmpty($definitions, '容器应该包含服务定义');

        // 验证是否加载了服务相关的定义
        $hasServiceDefinitions = false;
        foreach ($definitions as $definition) {
            $class = $definition->getClass();
            if (null !== $class && str_contains($class, 'CouponCommandBundle')) {
                $hasServiceDefinitions = true;
                break;
            }
        }
        $this->assertTrue($hasServiceDefinitions, '应该加载CouponCommandBundle相关服务');
    }
}
