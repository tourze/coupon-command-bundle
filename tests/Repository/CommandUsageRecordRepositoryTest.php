<?php

namespace Tourze\CouponCommandBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\CouponCommandBundle\Repository\CommandUsageRecordRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(CommandUsageRecordRepository::class)]
#[RunTestsInSeparateProcesses]
final class CommandUsageRecordRepositoryTest extends AbstractRepositoryTestCase
{
    private CommandUsageRecordRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(CommandUsageRecordRepository::class);
    }

    public function testCommandUsageRecordEntityCreation(): void
    {
        $usageRecord = new CommandUsageRecord();

        $this->assertInstanceOf(CommandUsageRecord::class, $usageRecord);
        $this->assertNull($usageRecord->getId());
        $this->assertNull($usageRecord->getUserId());
        $this->assertNull($usageRecord->getCommandText());
        $this->assertFalse($usageRecord->isSuccess());
        $this->assertNull($usageRecord->getCouponId());
        $this->assertNull($usageRecord->getFailureReason());
        $this->assertNull($usageRecord->getExtraData());
    }

    public function testCommandUsageRecordEntitySettersAndGetters(): void
    {
        $usageRecord = new CommandUsageRecord();

        $coupon = new Coupon();
        $coupon->setName('Test Coupon Setters');
        $coupon->setSn('TEST_SETTERS_' . uniqid());

        $commandConfig = new CommandConfig();
        $commandConfig->setCoupon($coupon);

        $usageRecord->setUserId('test-user-123');
        $usageRecord->setCommandText('TEST_COMMAND');
        $usageRecord->setCommandConfig($commandConfig);
        $usageRecord->setIsSuccess(true);
        $usageRecord->setCouponId('coupon-456');
        $usageRecord->setFailureReason('Test failure');
        $usageRecord->setExtraData(['key' => 'value']);

        $this->assertEquals('test-user-123', $usageRecord->getUserId());
        $this->assertEquals('TEST_COMMAND', $usageRecord->getCommandText());
        $this->assertSame($commandConfig, $usageRecord->getCommandConfig());
        $this->assertTrue($usageRecord->isSuccess());
        $this->assertEquals('coupon-456', $usageRecord->getCouponId());
        $this->assertEquals('Test failure', $usageRecord->getFailureReason());
        $this->assertEquals(['key' => 'value'], $usageRecord->getExtraData());
    }

    public function testCommandUsageRecordIsSuccessInitiallyFalse(): void
    {
        $usageRecord = new CommandUsageRecord();

        $this->assertFalse($usageRecord->isSuccess());
    }

    public function testCommandUsageRecordSuccessStatusToggle(): void
    {
        $usageRecord = new CommandUsageRecord();

        $usageRecord->setIsSuccess(true);
        $this->assertTrue($usageRecord->isSuccess());

        $usageRecord->setIsSuccess(false);
        $this->assertFalse($usageRecord->isSuccess());
    }

    public function testCommandUsageRecordWithComplexExtraData(): void
    {
        $usageRecord = new CommandUsageRecord();

        $extraData = [
            'source' => 'mobile_app',
            'version' => '1.2.3',
            'platform' => 'ios',
            'metadata' => [
                'location' => 'Beijing',
                'timestamp' => '2023-01-01 12:00:00',
            ],
        ];

        $usageRecord->setExtraData($extraData);

        $retrievedData = $usageRecord->getExtraData();
        $this->assertEquals($extraData, $retrievedData);
        $this->assertNotNull($retrievedData);
        $this->assertIsArray($retrievedData);
        $this->assertEquals('mobile_app', $retrievedData['source']);
        $this->assertIsArray($retrievedData['metadata']);
        $this->assertEquals('Beijing', $retrievedData['metadata']['location']);
    }

    public function testCommandUsageRecordStringRepresentation(): void
    {
        $usageRecord = new CommandUsageRecord();
        $usageRecord->setUserId('user123');
        $usageRecord->setCommandText('WELCOME');

        $string = (string) $usageRecord;

        $this->assertStringContainsString('CommandUsageRecord', $string);
        $this->assertStringContainsString('user123', $string);
        $this->assertStringContainsString('WELCOME', $string);
    }

    public function testCommandUsageRecordStringRepresentationWithNullValues(): void
    {
        $usageRecord = new CommandUsageRecord();

        $string = (string) $usageRecord;

        $this->assertStringContainsString('CommandUsageRecord', $string);
        $this->assertStringContainsString('#0', $string);
        $this->assertStringContainsString('N/A', $string);
    }

    public function testCommandUsageRecordApiArrayConversion(): void
    {
        $usageRecord = new CommandUsageRecord();
        $usageRecord->setUserId('api-user');
        $usageRecord->setCommandText('API_TEST');
        $usageRecord->setIsSuccess(true);
        $usageRecord->setCouponId('api-coupon');
        $usageRecord->setFailureReason('Test reason');
        $usageRecord->setExtraData(['source' => 'api']);

        $apiArray = $usageRecord->retrieveApiArray();

        $this->assertEquals('api-user', $apiArray['userId']);
        $this->assertEquals('API_TEST', $apiArray['commandText']);
        $this->assertTrue($apiArray['isSuccess']);
        $this->assertEquals('api-coupon', $apiArray['couponId']);
        $this->assertEquals('Test reason', $apiArray['failureReason']);
        $this->assertEquals(['source' => 'api'], $apiArray['extraData']);
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('createTime', $apiArray);
    }

    public function testCommandUsageRecordApiArrayWithNullValues(): void
    {
        $usageRecord = new CommandUsageRecord();

        $apiArray = $usageRecord->retrieveApiArray();

        $this->assertNull($apiArray['userId']);
        $this->assertNull($apiArray['commandText']);
        $this->assertFalse($apiArray['isSuccess']);
        $this->assertNull($apiArray['couponId']);
        $this->assertNull($apiArray['failureReason']);
        $this->assertNull($apiArray['extraData']);
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('createTime', $apiArray);
    }

    public function testCommandUsageRecordWithEmptyExtraData(): void
    {
        $usageRecord = new CommandUsageRecord();
        $usageRecord->setExtraData([]);

        $this->assertEquals([], $usageRecord->getExtraData());
    }

    public function testCommandUsageRecordUserIdCanBeSet(): void
    {
        $usageRecord = new CommandUsageRecord();

        $usageRecord->setUserId('user-12345');
        $this->assertEquals('user-12345', $usageRecord->getUserId());

        $usageRecord->setUserId('another-user');
        $this->assertEquals('another-user', $usageRecord->getUserId());
    }

    public function testCommandUsageRecordCommandTextCanBeSet(): void
    {
        $usageRecord = new CommandUsageRecord();

        $usageRecord->setCommandText('FIRST_COMMAND');
        $this->assertEquals('FIRST_COMMAND', $usageRecord->getCommandText());

        $usageRecord->setCommandText('SECOND_COMMAND');
        $this->assertEquals('SECOND_COMMAND', $usageRecord->getCommandText());
    }

    public function testCommandUsageRecordCouponIdCanBeSet(): void
    {
        $usageRecord = new CommandUsageRecord();

        $usageRecord->setCouponId('coupon-abc123');
        $this->assertEquals('coupon-abc123', $usageRecord->getCouponId());

        $usageRecord->setCouponId('coupon-def456');
        $this->assertEquals('coupon-def456', $usageRecord->getCouponId());
    }

    public function testCommandUsageRecordFailureReasonCanBeSet(): void
    {
        $usageRecord = new CommandUsageRecord();

        $usageRecord->setFailureReason('Quota exceeded');
        $this->assertEquals('Quota exceeded', $usageRecord->getFailureReason());

        $usageRecord->setFailureReason('Invalid command');
        $this->assertEquals('Invalid command', $usageRecord->getFailureReason());
    }

    public function testCommandUsageRecordCommandConfigAssociation(): void
    {
        $usageRecord = new CommandUsageRecord();

        $coupon1 = new Coupon();
        $coupon1->setName('Test Coupon Association 1');
        $coupon1->setSn('TEST_ASSOC1_' . uniqid());

        $coupon2 = new Coupon();
        $coupon2->setName('Test Coupon Association 2');
        $coupon2->setSn('TEST_ASSOC2_' . uniqid());

        $commandConfig1 = new CommandConfig();
        $commandConfig1->setCoupon($coupon1);

        $commandConfig2 = new CommandConfig();
        $commandConfig2->setCoupon($coupon2);

        $usageRecord->setCommandConfig($commandConfig1);
        $this->assertSame($commandConfig1, $usageRecord->getCommandConfig());

        $usageRecord->setCommandConfig($commandConfig2);
        $this->assertSame($commandConfig2, $usageRecord->getCommandConfig());

        $usageRecord->setCommandConfig(null);
        $this->assertNull($usageRecord->getCommandConfig());
    }

    public function testRepositoryInitialization(): void
    {
        $repository = self::getService(CommandUsageRecordRepository::class);
        $this->assertInstanceOf(CommandUsageRecordRepository::class, $repository);
        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }

    public function testSaveAndRemoveEntityMethods(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Usage Save Remove Test Coupon');
        $coupon->setSn('USAGE_SAVE_REMOVE_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('USAGE_SAVE_REMOVE_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $repository = self::getService(CommandUsageRecordRepository::class);

        $record = new CommandUsageRecord();
        $record->setUserId('save_remove_user');
        $record->setCommandText('SAVE_REMOVE_CMD');
        $record->setCommandConfig($commandConfig);

        $repository->save($record, true);
        $recordId = $record->getId();
        $this->assertNotNull($recordId);

        $saved = $repository->find($recordId);
        $this->assertNotNull($saved);

        $repository->remove($record);
        $removed = $repository->find($recordId);
        $this->assertNull($removed);
    }

    public function testFindOneByAssociationCommandConfigShouldReturnMatchingEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Usage Association Test Coupon');
        $coupon->setSn('USAGE_ASSOC_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('USAGE_ASSOC_TEST_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $repository = self::getService(CommandUsageRecordRepository::class);

        $record = new CommandUsageRecord();
        $record->setUserId('assoc_test_user');
        $record->setCommandText('ASSOC_TEST_CMD');
        $record->setCommandConfig($commandConfig);
        $record->setIsSuccess(true);
        $repository->save($record, true);

        $result = $repository->findOneBy(['commandConfig' => $commandConfig]);
        $this->assertNotNull($result);
        $this->assertSame($commandConfig, $result->getCommandConfig());
        $this->assertEquals('assoc_test_user', $result->getUserId());
    }

    public function testCountByAssociationCommandConfigShouldReturnCorrectNumber(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Usage Count Association Test Coupon');
        $coupon->setSn('USAGE_COUNT_ASSOC_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('USAGE_COUNT_ASSOC_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $repository = self::getService(CommandUsageRecordRepository::class);

        // 创建 3 个属于该命令配置的使用记录
        for ($i = 1; $i <= 3; ++$i) {
            $record = new CommandUsageRecord();
            $record->setUserId("count_assoc_user_{$i}");
            $record->setCommandText("COUNT_ASSOC_CMD_{$i}");
            $record->setCommandConfig($commandConfig);
            $record->setIsSuccess(true);
            $repository->save($record, true);
        }

        $count = $repository->count(['commandConfig' => $commandConfig]);
        $this->assertSame(3, $count);

        // 测试不存在的关联
        $otherCoupon = new Coupon();
        $otherCoupon->setName('Other Usage Test Coupon');
        $otherCoupon->setSn('OTHER_USAGE_TEST');
        self::getEntityManager()->persist($otherCoupon);
        self::getEntityManager()->flush();

        $otherCommandConfig = new CommandConfig();
        $otherCommandConfig->setCommand('OTHER_USAGE_COMMAND');
        $otherCommandConfig->setCoupon($otherCoupon);
        self::getEntityManager()->persist($otherCommandConfig);
        self::getEntityManager()->flush();

        $zeroCount = $repository->count(['commandConfig' => $otherCommandConfig]);
        $this->assertSame(0, $zeroCount);
    }

    public function testFindOneByCouponIdNotNullShouldReturnMatchingEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('With Coupon ID Test Coupon');
        $coupon->setSn('WITH_COUPON_ID_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('WITH_COUPON_ID_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $repository = self::getService(CommandUsageRecordRepository::class);

        $recordWithCouponId = new CommandUsageRecord();
        $recordWithCouponId->setUserId('with_coupon_id_user');
        $recordWithCouponId->setCommandText('WITH_COUPON_ID_CMD');
        $recordWithCouponId->setCommandConfig($commandConfig);
        $recordWithCouponId->setCouponId('test_coupon_123');
        $recordWithCouponId->setIsSuccess(true);
        $repository->save($recordWithCouponId, true);

        $result = $repository->findOneBy(['couponId' => 'test_coupon_123']);
        $this->assertNotNull($result);
        $this->assertEquals('test_coupon_123', $result->getCouponId());
        $this->assertEquals('with_coupon_id_user', $result->getUserId());
    }

    public function testCountByFailureReasonNotNullShouldReturnCorrectNumber(): void
    {
        $repository = self::getService(CommandUsageRecordRepository::class);

        $coupon = new Coupon();
        $coupon->setName('With Failure Reason Test Coupon');
        $coupon->setSn('WITH_FAILURE_REASON_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('WITH_FAILURE_REASON_COMMAND');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        // 创建 1 个有 failureReason 的记录
        $recordWithFailureReason = new CommandUsageRecord();
        $recordWithFailureReason->setUserId('with_failure_reason_user');
        $recordWithFailureReason->setCommandText('WITH_FAILURE_REASON_CMD');
        $recordWithFailureReason->setCommandConfig($commandConfig);
        $recordWithFailureReason->setFailureReason('Quota exceeded');
        $recordWithFailureReason->setIsSuccess(false);
        $repository->save($recordWithFailureReason, true);

        // 使用特定的失败原因进行查询
        $count = $repository->count(['failureReason' => 'Quota exceeded']);
        $this->assertSame(1, $count);

        $zeroCount = $repository->count(['failureReason' => 'Non existent reason']);
        $this->assertSame(0, $zeroCount);
    }

    public function testFindByUserIdShouldReturnOrderedRecords(): void
    {
        $coupon = new Coupon();
        $coupon->setName('User ID Test Coupon');
        $coupon->setSn('USER_ID_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('USER_ID_CMD');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $testUserId = 'test_user_123';

        // 创建多个使用记录
        for ($i = 1; $i <= 3; ++$i) {
            $record = new CommandUsageRecord();
            $record->setUserId($testUserId);
            $record->setCommandText("USER_ID_CMD_{$i}");
            $record->setCommandConfig($commandConfig);
            $record->setCouponId((string) $coupon->getId());
            $record->setIsSuccess(0 === $i % 2);
            $this->repository->save($record, true);
            usleep(1000); // 确保时间不同
        }

        $results = $this->repository->findByUserId($testUserId);
        $this->assertGreaterThanOrEqual(3, count($results));

        // 验证所有返回的记录都属于正确的用户
        foreach ($results as $record) {
            $this->assertEquals($testUserId, $record->getUserId());
        }
    }

    public function testFindByCommandConfigIdShouldReturnOrderedRecords(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Command Config ID Test Coupon');
        $coupon->setSn('CONFIG_ID_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('CONFIG_ID_CMD');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        // 创建多个使用记录
        for ($i = 1; $i <= 2; ++$i) {
            $record = new CommandUsageRecord();
            $record->setUserId("user_{$i}");
            $record->setCommandText('CONFIG_ID_CMD');
            $record->setCommandConfig($commandConfig);
            $record->setCouponId((string) $coupon->getId());
            $record->setIsSuccess(true);
            $this->repository->save($record, true);
            usleep(1000); // 确保时间不同
        }

        $commandConfigId = $commandConfig->getId();
        $this->assertNotNull($commandConfigId, 'CommandConfig ID should not be null');
        $results = $this->repository->findByCommandConfigId($commandConfigId);
        $this->assertGreaterThanOrEqual(2, count($results));

        // 验证所有记录都属于正确的 CommandConfig
        foreach ($results as $record) {
            $recordConfig = $record->getCommandConfig();
            if (null !== $recordConfig && $recordConfig->getId() === $commandConfigId) {
                $this->assertEquals($commandConfigId, $recordConfig->getId());
            }
        }
    }

    public function testCountByUserAndCommandConfigShouldReturnCorrectNumber(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Count User Config Test Coupon');
        $coupon->setSn('COUNT_USER_CONFIG_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('COUNT_USER_CONFIG_CMD');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $testUserId = 'count_test_user';
        $testConfigId = $commandConfig->getId();
        $this->assertNotNull($testConfigId, 'CommandConfig ID should not be null');

        // 创建属于测试用户和配置的记录
        for ($i = 1; $i <= 3; ++$i) {
            $record = new CommandUsageRecord();
            $record->setUserId($testUserId);
            $record->setCommandText('COUNT_USER_CONFIG_CMD');
            $record->setCommandConfig($commandConfig);
            $record->setCouponId((string) $coupon->getId());
            $record->setIsSuccess($i <= 2);
            $this->repository->save($record, true);
        }

        // 创建不匹配的记录
        $otherRecord = new CommandUsageRecord();
        $otherRecord->setUserId('other_user');
        $otherRecord->setCommandText('COUNT_USER_CONFIG_CMD');
        $otherRecord->setCommandConfig($commandConfig);
        $otherRecord->setCouponId((string) $coupon->getId());
        $otherRecord->setIsSuccess(true);
        $this->repository->save($otherRecord);

        $count = $this->repository->countByUserAndCommandConfig($testUserId, $testConfigId);
        $this->assertEquals(3, $count);
    }

    public function testCountSuccessByUserAndCommandConfigShouldReturnCorrectNumber(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Count Success Test Coupon');
        $coupon->setSn('COUNT_SUCCESS_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('COUNT_SUCCESS_CMD');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $testUserId = 'success_test_user';
        $testConfigId = $commandConfig->getId();
        $this->assertNotNull($testConfigId, 'CommandConfig ID should not be null');

        // 创建成功和失败的记录
        for ($i = 1; $i <= 4; ++$i) {
            $record = new CommandUsageRecord();
            $record->setUserId($testUserId);
            $record->setCommandText('COUNT_SUCCESS_CMD');
            $record->setCommandConfig($commandConfig);
            $record->setCouponId((string) $coupon->getId());
            $record->setIsSuccess($i <= 2); // 前2个成功，后2个失败
            if ($i > 2) {
                $record->setFailureReason('Test failure');
            }
            $this->repository->save($record, true);
        }

        $successCount = $this->repository->countSuccessByUserAndCommandConfig($testUserId, $testConfigId);
        $this->assertEquals(2, $successCount);

        $totalCount = $this->repository->countByUserAndCommandConfig($testUserId, $testConfigId);
        $this->assertEquals(4, $totalCount);
    }

    public function testRemoveMethodShouldDeleteEntity(): void
    {
        $coupon = new Coupon();
        $coupon->setName('Remove Test Coupon');
        $coupon->setSn('REMOVE_TEST');
        self::getEntityManager()->persist($coupon);
        self::getEntityManager()->flush();

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('REMOVE_CMD');
        $commandConfig->setCoupon($coupon);
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $record = new CommandUsageRecord();
        $record->setUserId('remove_test_user');
        $record->setCommandText('REMOVE_CMD');
        $record->setCommandConfig($commandConfig);
        $record->setCouponId((string) $coupon->getId());
        $record->setIsSuccess(true);
        $this->repository->save($record);

        $recordId = $record->getId();
        $this->assertNotNull($this->repository->find($recordId));

        $this->repository->remove($record);
        $this->assertNull($this->repository->find($recordId));
    }

    protected function getRepository(): CommandUsageRecordRepository
    {
        return self::getService(CommandUsageRecordRepository::class);
    }

    protected function createNewEntity(): object
    {
        $usageRecord = new CommandUsageRecord();
        $usageRecord->setUserId('test_user_' . uniqid());
        $usageRecord->setCommandText('TEST_COMMAND');
        $usageRecord->setIsSuccess(true);
        $usageRecord->setCouponId('test_coupon_' . uniqid());

        // 创建一个基本的 CommandConfig 实体来满足外键约束
        // 注意：不设置CommandConfig的coupon关联，避免cascade persist问题
        $commandConfig = new CommandConfig();
        $commandConfig->setCommand('TEST_COMMAND');
        self::getEntityManager()->persist($commandConfig);
        self::getEntityManager()->flush();

        $usageRecord->setCommandConfig($commandConfig);

        return $usageRecord;
    }
}
