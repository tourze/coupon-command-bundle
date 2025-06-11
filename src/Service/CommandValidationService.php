<?php

namespace Tourze\CouponCommandBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCommandBundle\Repository\CommandUsageRecordRepository;

class CommandValidationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommandConfigRepository $commandConfigRepository,
        private readonly CommandUsageRecordRepository $usageRecordRepository,
    ) {}

    /**
     * 验证口令有效性
     */
    public function validateCommand(string $command, ?string $userId = null): array
    {
        // 查找口令配置
        $commandConfig = $this->commandConfigRepository->findOneBy(['command' => $command]);
        if (!$commandConfig) {
            return [
                'valid' => false,
                'reason' => '口令不存在',
            ];
        }

        // 检查优惠券是否存在
        if (!$commandConfig->getCoupon()) {
            return [
                'valid' => false,
                'reason' => '优惠券不存在',
            ];
        }

        // 检查限制配置
        $commandLimit = $commandConfig->getCommandLimit();
        if ($commandLimit && $commandLimit->isEnabled()) {
            $limitResult = $this->validateLimits($commandLimit, $commandConfig, $userId);
            if (!$limitResult['valid']) {
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
     */
    public function useCommand(string $command, string $userId): array
    {
        // 先验证口令
        $validationResult = $this->validateCommand($command, $userId);
        if (!$validationResult['valid']) {
            // 记录失败的使用记录
            $this->recordUsage($command, $userId, false, $validationResult['reason']);
            return [
                'success' => false,
                'message' => $validationResult['reason'],
            ];
        }

        $commandConfig = $this->commandConfigRepository->findOneBy(['command' => $command]);

        try {
            $this->entityManager->beginTransaction();

            // 增加使用次数计数
            if ($commandConfig->getCommandLimit()) {
                $commandConfig->getCommandLimit()->incrementUsage();
                $this->entityManager->persist($commandConfig->getCommandLimit());
            }

            // 记录成功的使用记录
            $couponId = $commandConfig->getCoupon()->getId();
            $this->recordUsage($command, $userId, true, null, $couponId);

            $this->entityManager->commit();

            return [
                'success' => true,
                'couponId' => $couponId,
                'message' => '优惠券领取成功',
            ];
        } catch  (\Throwable $e) {
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
     */
    private function validateLimits(CommandLimit $limit, CommandConfig $commandConfig, ?string $userId): array
    {
        // 检查时间限制
        if (!$limit->isTimeValid()) {
            return [
                'valid' => false,
                'reason' => '口令使用时间超出有效期',
            ];
        }

        // 检查总使用次数限制
        if (!$limit->hasTotalUsageQuota()) {
            return [
                'valid' => false,
                'reason' => '口令使用次数已达上限',
            ];
        }

        // 如果提供了用户ID，检查用户相关限制
        if ($userId !== null) {
            // 检查用户是否在允许列表中
            if (!$limit->isUserAllowed($userId)) {
                return [
                    'valid' => false,
                    'reason' => '您不在此口令的使用范围内',
                ];
            }

            // 检查用户使用次数限制
            if ($limit->getMaxUsagePerUser() !== null) {
                $userUsageCount = $this->usageRecordRepository->countSuccessByUserAndCommandConfig(
                    $userId,
                    $commandConfig->getId()
                );

                if ($userUsageCount >= $limit->getMaxUsagePerUser()) {
                    return [
                        'valid' => false,
                        'reason' => '您已达到此口令的使用次数上限',
                    ];
                }
            }
        }

        return ['valid' => true];
    }

    /**
     * 记录使用情况
     */
    private function recordUsage(
        string $command,
        string $userId,
        bool $isSuccess,
        ?string $failureReason = null,
        ?string $couponId = null
    ): void {
        $commandConfig = $this->commandConfigRepository->findOneBy(['command' => $command]);

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
     */
    public function getUserUsageRecords(string $userId): array
    {
        return $this->usageRecordRepository->findByUserId($userId);
    }

    /**
     * 获取口令的使用记录
     */
    public function getCommandUsageRecords(string $commandConfigId): array
    {
        return $this->usageRecordRepository->findByCommandConfigId($commandConfigId);
    }
}
