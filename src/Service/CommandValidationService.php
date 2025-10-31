<?php

namespace Tourze\CouponCommandBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\CouponCommandBundle\Exception\CommandConfigurationException;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCommandBundle\Repository\CommandUsageRecordRepository;

#[Autoconfigure(public: true)]
readonly class CommandValidationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommandConfigRepository $commandConfigRepository,
        private CommandUsageRecordRepository $usageRecordRepository,
    ) {
    }

    /**
     * 验证口令有效性
     *
     * @return array<string, mixed>
     */
    public function validateCommand(string $command, ?string $userId = null): array
    {
        // 查找口令配置
        $commandConfig = $this->commandConfigRepository->findOneBy(['command' => $command]);
        if (null === $commandConfig) {
            return [
                'valid' => false,
                'reason' => '口令不存在',
            ];
        }

        // 检查优惠券是否存在
        if (null === $commandConfig->getCoupon()) {
            return [
                'valid' => false,
                'reason' => '优惠券不存在',
            ];
        }

        // 检查限制配置
        $commandLimit = $commandConfig->getCommandLimit();
        if (null !== $commandLimit && $commandLimit->isEnabled()) {
            $limitResult = $this->validateLimits($commandLimit, $commandConfig, $userId);
            if (!(bool) $limitResult['valid']) {
                return $limitResult;
            }
        }

        return [
            'valid' => true,
            'couponInfo' => $commandConfig->getCoupon()->retrieveApiArray(),
            'commandConfig' => $commandConfig->retrieveApiArray(),
        ];
    }

    /**
     * 使用口令领取优惠券
     *
     * @return array<string, mixed>
     */
    public function useCommand(string $command, string $userId): array
    {
        // 先验证口令
        $validationResult = $this->validateCommand($command, $userId);
        if (!(bool) $validationResult['valid']) {
            // 记录失败的使用记录
            $reason = isset($validationResult['reason']) && is_string($validationResult['reason'])
                ? $validationResult['reason']
                : '验证失败';
            $this->recordUsage($command, $userId, false, $reason);

            return [
                'success' => false,
                'message' => $reason,
            ];
        }

        $commandConfig = $this->commandConfigRepository->findOneBy(['command' => $command]);
        if (null === $commandConfig) {
            return [
                'success' => false,
                'message' => '口令不存在',
            ];
        }

        try {
            $this->entityManager->beginTransaction();

            // 增加使用次数计数
            if (null !== $commandConfig->getCommandLimit()) {
                $commandConfig->getCommandLimit()->incrementUsage();
                $this->entityManager->persist($commandConfig->getCommandLimit());
            }

            // 记录成功的使用记录
            $coupon = $commandConfig->getCoupon();
            if (null === $coupon) {
                throw new CommandConfigurationException('口令配置没有关联的优惠券');
            }
            $couponId = (string) $coupon->getId();
            $this->recordUsage($command, $userId, true, null, $couponId);

            $this->entityManager->commit();

            return [
                'success' => true,
                'couponId' => $couponId,
                'message' => '优惠券领取成功',
            ];
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            // 记录失败的使用记录
            $this->recordUsage($command, $userId, false, '系统错误: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => '系统错误，请稍后重试',
            ];
        }
    }

    /**
     * 验证限制条件
     *
     * @return array<string, mixed>
     */
    private function validateLimits(CommandLimit $limit, CommandConfig $commandConfig, ?string $userId): array
    {
        // 检查时间限制
        if (!$limit->isTimeValid()) {
            return $this->createValidationResult(false, '口令使用时间超出有效期');
        }

        // 检查总使用次数限制
        if (!$limit->hasTotalUsageQuota()) {
            return $this->createValidationResult(false, '口令使用次数已达上限');
        }

        // 如果提供了用户ID，检查用户相关限制
        if (null !== $userId) {
            $userValidation = $this->validateUserLimits($limit, $commandConfig, $userId);
            if (!(bool) $userValidation['valid']) {
                return $userValidation;
            }
        }

        return $this->createValidationResult(true);
    }

    /** @return array<string, mixed> */
    private function createValidationResult(bool $valid, ?string $reason = null): array
    {
        $result = ['valid' => $valid];
        if (null !== $reason) {
            $result['reason'] = $reason;
        }

        return $result;
    }

    /** @return array<string, mixed> */
    private function validateUserLimits(CommandLimit $limit, CommandConfig $commandConfig, string $userId): array
    {
        // 检查用户是否在允许列表中
        if (!$limit->isUserAllowed($userId)) {
            return $this->createValidationResult(false, '您不在此口令的使用范围内');
        }

        // 检查用户使用次数限制
        if (null !== $limit->getMaxUsagePerUser()) {
            $configId = $commandConfig->getId();
            if (null === $configId) {
                return $this->createValidationResult(false, '口令配置无效');
            }
            $userUsageCount = $this->usageRecordRepository->countSuccessByUserAndCommandConfig(
                $userId,
                $configId
            );

            if ($userUsageCount >= $limit->getMaxUsagePerUser()) {
                return $this->createValidationResult(false, '您已达到此口令的使用次数上限');
            }
        }

        return $this->createValidationResult(true);
    }

    /**
     * 记录使用情况
     */
    private function recordUsage(
        string $command,
        string $userId,
        bool $isSuccess,
        ?string $failureReason = null,
        ?string $couponId = null,
    ): void {
        $commandConfig = $this->commandConfigRepository->findOneBy(['command' => $command]);

        if (null === $commandConfig) {
            return;
        }

        $usageRecord = new CommandUsageRecord();
        $usageRecord->setCommandConfig($commandConfig);
        $usageRecord->setUserId($userId);
        $usageRecord->setCommandText($command);
        $usageRecord->setIsSuccess($isSuccess);
        $usageRecord->setFailureReason($failureReason);
        $usageRecord->setCouponId($couponId);

        $this->entityManager->persist($usageRecord);
        $this->entityManager->flush();
    }

    /**
     * 获取用户的使用记录
     *
     * @return array<int, CommandUsageRecord>
     */
    public function getUserUsageRecords(string $userId): array
    {
        return $this->usageRecordRepository->findByUserId($userId);
    }

    /**
     * 获取口令的使用记录
     *
     * @return array<int, CommandUsageRecord>
     */
    public function getCommandUsageRecords(string $commandConfigId): array
    {
        return $this->usageRecordRepository->findByCommandConfigId($commandConfigId);
    }
}
