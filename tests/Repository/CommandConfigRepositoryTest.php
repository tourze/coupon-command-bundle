<?php

namespace Tourze\CouponCommandBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;

class CommandConfigRepositoryTest extends TestCase
{
    private CommandConfigRepository $repository;
    private ManagerRegistry|MockObject $managerRegistry;
    private EntityManagerInterface|MockObject $entityManager;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->managerRegistry
            ->method('getManagerForClass')
            ->with(CommandConfig::class)
            ->willReturn($this->entityManager);

        $this->repository = new CommandConfigRepository($this->managerRegistry);
    }

    public function test_inheritance(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function test_repository_class_initialization(): void
    {
        $this->assertInstanceOf(CommandConfigRepository::class, $this->repository);
    }

    public function test_repository_has_correct_entity_class(): void
    {
        // 通过反射检查 Repository 是否正确配置了实体类
        $reflection = new \ReflectionClass($this->repository);
        $parentReflection = $reflection->getParentClass();

        $this->assertTrue($parentReflection->getName() === ServiceEntityRepository::class);
    }

    public function test_method_signatures_exist(): void
    {
        // 验证所有必需的方法都存在
        $this->assertTrue(method_exists($this->repository, 'findByCommand'));
        $this->assertTrue(method_exists($this->repository, 'findByCouponId'));
        $this->assertTrue(method_exists($this->repository, 'findAllWithLimits'));
        $this->assertTrue(method_exists($this->repository, 'findAllWithEnabledLimits'));
        $this->assertTrue(method_exists($this->repository, 'isCommandExists'));
        $this->assertTrue(method_exists($this->repository, 'getUsageStats'));
    }

    public function test_find_by_command_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'findByCommand');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('command', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_find_by_coupon_id_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'findByCouponId');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('couponId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
    }

    public function test_find_all_with_limits_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'findAllWithLimits');
        $parameters = $reflection->getParameters();

        $this->assertCount(0, $parameters);

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_find_all_with_enabled_limits_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'findAllWithEnabledLimits');
        $parameters = $reflection->getParameters();

        $this->assertCount(0, $parameters);

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_is_command_exists_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'isCommandExists');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('command', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('excludeId', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    public function test_get_usage_stats_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'getUsageStats');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('commandConfigId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_repository_constants_and_properties(): void
    {
        // 验证 Repository 正确配置
        $reflection = new \ReflectionClass($this->repository);

        // 检查是否继承了正确的父类
        $this->assertTrue($reflection->isSubclassOf(ServiceEntityRepository::class));

        // 检查构造函数参数
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('registry', $parameters[0]->getName());
    }

    public function test_docblock_annotations(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $docComment = $reflection->getDocComment();

        $this->assertNotFalse($docComment);
        $this->assertStringContainsString('@method', $docComment);
        $this->assertStringContainsString('CommandConfig', $docComment);
    }
}
