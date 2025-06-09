<?php

namespace Tourze\CouponCommandBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCommandBundle\Repository\CommandLimitRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;

class CommandManagementService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommandConfigRepository $commandConfigRepository,
        private readonly CommandLimitRepository $commandLimitRepository,
    ) {}

    /**
     * 创建口令配置
     */
    public function createCommandConfig(string $command, Coupon $coupon): CommandConfig
    {
        // 检查口令是否重复
        if ($this->commandConfigRepository->isCommandExists($command)) {
            throw new \InvalidArgumentException('口令已存在');
        }

        $commandConfig = new CommandConfig();
        $commandConfig->setCommand($command);
        $commandConfig->setCoupon($coupon);

        $this->entityManager->persist($commandConfig);
        $this->entityManager->flush();

        return $commandConfig;
    }

    /**
     * 更新口令配置
     */
    public function updateCommandConfig(string $id, string $command): CommandConfig
    {
        $commandConfig = $this->commandConfigRepository->find($id);
        if (!$commandConfig) {
            throw new \InvalidArgumentException('口令配置不存在');
        }

        // 检查口令是否重复
        if ($this->commandConfigRepository->isCommandExists($command, $id)) {
            throw new \InvalidArgumentException('口令已存在');
        }

        $commandConfig->setCommand($command);

        $this->entityManager->persist($commandConfig);
        $this->entityManager->flush();

        return $commandConfig;
    }

    /**
     * 删除口令配置
     */
    public function deleteCommandConfig(string $id): bool
    {
        $commandConfig = $this->commandConfigRepository->find($id);
        if (!$commandConfig) {
            return false;
        }

        $this->entityManager->remove($commandConfig);
        $this->entityManager->flush();

        return true;
    }

    /**
     * 为口令配置添加限制
     */
    public function addCommandLimit(
        string $commandConfigId,
        ?int $maxUsagePerUser = null,
        ?int $maxTotalUsage = null,
        ?\DateTimeInterface $startTime = null,
        ?\DateTimeInterface $endTime = null,
        ?array $allowedUsers = null,
        ?array $allowedUserTags = null
    ): CommandLimit {
        $commandConfig = $this->commandConfigRepository->find($commandConfigId);
        if (!$commandConfig) {
            throw new \InvalidArgumentException('口令配置不存在');
        }

        // 检查是否已有限制配置
        if ($commandConfig->getCommandLimit()) {
            throw new \InvalidArgumentException('该口令已有限制配置');
        }

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxUsagePerUser($maxUsagePerUser);
        $commandLimit->setMaxTotalUsage($maxTotalUsage);
        $commandLimit->setStartTime($startTime);
        $commandLimit->setEndTime($endTime);
        $commandLimit->setAllowedUsers($allowedUsers);
        $commandLimit->setAllowedUserTags($allowedUserTags);

        $this->entityManager->persist($commandLimit);
        $this->entityManager->flush();

        return $commandLimit;
    }

    /**
     * 更新口令限制
     */
    public function updateCommandLimit(
        string $commandLimitId,
        ?int $maxUsagePerUser = null,
        ?int $maxTotalUsage = null,
        ?\DateTimeInterface $startTime = null,
        ?\DateTimeInterface $endTime = null,
        ?array $allowedUsers = null,
        ?array $allowedUserTags = null,
        ?bool $isEnabled = null
    ): CommandLimit {
        $commandLimit = $this->commandLimitRepository->find($commandLimitId);
        if (!$commandLimit) {
            throw new \InvalidArgumentException('限制配置不存在');
        }

        if ($maxUsagePerUser !== null) {
            $commandLimit->setMaxUsagePerUser($maxUsagePerUser);
        }
        if ($maxTotalUsage !== null) {
            $commandLimit->setMaxTotalUsage($maxTotalUsage);
        }
        if ($startTime !== null) {
            $commandLimit->setStartTime($startTime);
        }
        if ($endTime !== null) {
            $commandLimit->setEndTime($endTime);
        }
        if ($allowedUsers !== null) {
            $commandLimit->setAllowedUsers($allowedUsers);
        }
        if ($allowedUserTags !== null) {
            $commandLimit->setAllowedUserTags($allowedUserTags);
        }
        if ($isEnabled !== null) {
            $commandLimit->setIsEnabled($isEnabled);
        }

        $this->entityManager->persist($commandLimit);
        $this->entityManager->flush();

        return $commandLimit;
    }

    /**
     * 删除口令限制
     */
    public function deleteCommandLimit(string $commandLimitId): bool
    {
        $commandLimit = $this->commandLimitRepository->find($commandLimitId);
        if (!$commandLimit) {
            return false;
        }

        $this->entityManager->remove($commandLimit);
        $this->entityManager->flush();

        return true;
    }

    /**
     * 获取口令配置详情
     */
    public function getCommandConfigDetail(string $id): ?array
    {
        $commandConfig = $this->commandConfigRepository->find($id);
        if (!$commandConfig) {
            return null;
        }

        $usageStats = $this->commandConfigRepository->getUsageStats($id);

        return [
            'config' => $commandConfig->retrieveApiArray(),
            'stats' => $usageStats,
        ];
    }

    /**
     * 获取所有口令配置列表
     */
    public function getCommandConfigList(): array
    {
        $configs = $this->commandConfigRepository->findAll();
        $result = [];

        foreach ($configs as $config) {
            $usageStats = $this->commandConfigRepository->getUsageStats($config->getId());
            $result[] = [
                'config' => $config->retrieveApiArray(),
                'stats' => $usageStats,
            ];
        }

        return $result;
    }

    /**
     * 启用或禁用口令限制
     */
    public function toggleCommandLimitStatus(string $commandLimitId): CommandLimit
    {
        $commandLimit = $this->commandLimitRepository->find($commandLimitId);
        if (!$commandLimit) {
            throw new \InvalidArgumentException('限制配置不存在');
        }

        $commandLimit->setIsEnabled(!$commandLimit->isEnabled());

        $this->entityManager->persist($commandLimit);
        $this->entityManager->flush();

        return $commandLimit;
    }
}
