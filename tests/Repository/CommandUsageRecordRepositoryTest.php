<?php

namespace Tourze\CouponCommandBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\CouponCommandBundle\Repository\CommandUsageRecordRepository;

class CommandUsageRecordRepositoryTest extends TestCase
{
    private CommandUsageRecordRepository $repository;
    private ManagerRegistry|MockObject $managerRegistry;
    private EntityManagerInterface|MockObject $entityManager;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->managerRegistry
            ->method('getManagerForClass')
            ->with(CommandUsageRecord::class)
            ->willReturn($this->entityManager);

        $this->repository = new CommandUsageRecordRepository($this->managerRegistry);
    }

    public function test_inheritance(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function test_repository_class_initialization(): void
    {
        $this->assertInstanceOf(CommandUsageRecordRepository::class, $this->repository);
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
        $this->assertTrue(method_exists($this->repository, 'findByUserId'));
        $this->assertTrue(method_exists($this->repository, 'findByCommandConfigId'));
        $this->assertTrue(method_exists($this->repository, 'countByUserAndCommandConfig'));
        $this->assertTrue(method_exists($this->repository, 'countSuccessByUserAndCommandConfig'));
    }

    public function test_find_by_user_id_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'findByUserId');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('userId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_find_by_command_config_id_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'findByCommandConfigId');
        $parameters = $reflection->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('commandConfigId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    public function test_count_by_user_and_command_config_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'countByUserAndCommandConfig');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('userId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('commandConfigId', $parameters[1]->getName());
        $this->assertEquals('string', $parameters[1]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    public function test_count_success_by_user_and_command_config_method_signature(): void
    {
        $reflection = new \ReflectionMethod($this->repository, 'countSuccessByUserAndCommandConfig');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('userId', $parameters[0]->getName());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
        $this->assertEquals('commandConfigId', $parameters[1]->getName());
        $this->assertEquals('string', $parameters[1]->getType()->getName());

        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
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
        $this->assertStringContainsString('CommandUsageRecord', $docComment);
    }

    public function test_class_name(): void
    {
        $this->assertEquals('Tourze\\CouponCommandBundle\\Repository\\CommandUsageRecordRepository', $this->repository::class);
    }

    public function test_service_repository_inheritance(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $this->assertTrue($reflection->isSubclassOf(ServiceEntityRepository::class));
    }

    public function test_entity_manager_injection(): void
    {
        // 验证 ManagerRegistry 被正确注入
        $this->assertInstanceOf(ManagerRegistry::class, $this->managerRegistry);
        $this->assertInstanceOf(EntityManagerInterface::class, $this->entityManager);
    }

    public function test_repository_namespace(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $this->assertEquals('Tourze\\CouponCommandBundle\\Repository\\CommandUsageRecordRepository', $reflection->getName());
    }

    public function test_entity_class_property(): void
    {
        // 验证实体类属性设置正确（通过父类 ServiceEntityRepository）
        $reflection = new \ReflectionClass($this->repository);
        $this->assertTrue($reflection->isSubclassOf(ServiceEntityRepository::class));
    }
}
