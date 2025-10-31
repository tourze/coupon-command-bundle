<?php

namespace Tourze\CouponCommandBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Exception\CommandConfigurationException;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCommandBundle\Repository\CommandLimitRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;

#[Autoconfigure(public: true)]
readonly class CommandManagementService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommandConfigRepository $commandConfigRepository,
        private CommandLimitRepository $commandLimitRepository,
    ) {
    }

    /**
     * 创建口令配置
     */
    public function createCommandConfig(string $command, Coupon $coupon): CommandConfig
    {
        // 检查口令是否重复
        if ($this->commandConfigRepository->isCommandExists($command)) {
            throw new CommandConfigurationException('口令已存在');
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
        if (null === $commandConfig) {
            throw new CommandConfigurationException('口令配置不存在');
        }

        // 检查口令是否重复
        if ($this->commandConfigRepository->isCommandExists($command, $id)) {
            throw new CommandConfigurationException('口令已存在');
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
        if (null === $commandConfig) {
            return false;
        }

        $this->entityManager->remove($commandConfig);
        $this->entityManager->flush();

        return true;
    }

    /**
     * 为口令配置添加限制
     *
     * @param array<int, string>|null $allowedUsers
     * @param array<int, string>|null $allowedUserTags
     */
    public function addCommandLimit(
        string $commandConfigId,
        ?int $maxUsagePerUser = null,
        ?int $maxTotalUsage = null,
        ?\DateTimeInterface $startTime = null,
        ?\DateTimeInterface $endTime = null,
        ?array $allowedUsers = null,
        ?array $allowedUserTags = null,
    ): CommandLimit {
        $commandConfig = $this->commandConfigRepository->find($commandConfigId);
        if (null === $commandConfig) {
            throw new CommandConfigurationException('口令配置不存在');
        }

        // 检查是否已有限制配置
        if (null !== $commandConfig->getCommandLimit()) {
            throw new CommandConfigurationException('该口令已有限制配置');
        }

        $commandLimit = new CommandLimit();
        $commandLimit->setCommandConfig($commandConfig);
        $commandLimit->setMaxUsagePerUser($maxUsagePerUser);
        $commandLimit->setMaxTotalUsage($maxTotalUsage);
        $commandLimit->setStartTime(null !== $startTime && !$startTime instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromInterface($startTime) : $startTime);
        $commandLimit->setEndTime(null !== $endTime && !$endTime instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromInterface($endTime) : $endTime);
        $commandLimit->setAllowedUsers($allowedUsers);
        $commandLimit->setAllowedUserTags($allowedUserTags);

        $this->entityManager->persist($commandLimit);
        $this->entityManager->flush();

        return $commandLimit;
    }

    /**
     * 更新口令限制
     *
     * @param array<int, string>|null $allowedUsers
     * @param array<int, string>|null $allowedUserTags
     */
    public function updateCommandLimit(
        string $commandLimitId,
        ?int $maxUsagePerUser = null,
        ?int $maxTotalUsage = null,
        ?\DateTimeInterface $startTime = null,
        ?\DateTimeInterface $endTime = null,
        ?array $allowedUsers = null,
        ?array $allowedUserTags = null,
        ?bool $isEnabled = null,
    ): CommandLimit {
        $commandLimit = $this->commandLimitRepository->find($commandLimitId);
        if (null === $commandLimit) {
            throw new CommandConfigurationException('限制配置不存在');
        }

        $this->updateCommandLimitProperties($commandLimit, [
            'maxUsagePerUser' => $maxUsagePerUser,
            'maxTotalUsage' => $maxTotalUsage,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'allowedUsers' => $allowedUsers,
            'allowedUserTags' => $allowedUserTags,
            'isEnabled' => $isEnabled,
        ]);

        $this->entityManager->persist($commandLimit);
        $this->entityManager->flush();

        return $commandLimit;
    }

    /**
     * 更新命令限制属性
     *
     * @param array<string, mixed> $properties
     */
    private function updateCommandLimitProperties(CommandLimit $commandLimit, array $properties): void
    {
        $this->updateIntProperty($commandLimit, $properties, 'maxUsagePerUser', 'setMaxUsagePerUser');
        $this->updateIntProperty($commandLimit, $properties, 'maxTotalUsage', 'setMaxTotalUsage');
        $this->updateDateTimeProperty($commandLimit, $properties, 'startTime');
        $this->updateDateTimeProperty($commandLimit, $properties, 'endTime');
        $this->updateArrayProperty($commandLimit, $properties, 'allowedUsers', 'setAllowedUsers');
        $this->updateArrayProperty($commandLimit, $properties, 'allowedUserTags', 'setAllowedUserTags');
        $this->updateBoolProperty($commandLimit, $properties, 'isEnabled', 'setIsEnabled');
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function updateIntProperty(CommandLimit $commandLimit, array $properties, string $key, string $setter): void
    {
        if (!isset($properties[$key]) || !is_int($properties[$key])) {
            return;
        }

        match ($setter) {
            'setMaxUsagePerUser' => $commandLimit->setMaxUsagePerUser($properties[$key]),
            'setMaxTotalUsage' => $commandLimit->setMaxTotalUsage($properties[$key]),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function updateDateTimeProperty(CommandLimit $commandLimit, array $properties, string $key): void
    {
        if (isset($properties[$key])) {
            $value = $properties[$key];
            if ($value instanceof \DateTimeInterface) {
                if ('startTime' === $key) {
                    $this->setStartTime($commandLimit, $value);
                } elseif ('endTime' === $key) {
                    $this->setEndTime($commandLimit, $value);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function updateArrayProperty(CommandLimit $commandLimit, array $properties, string $key, string $setter): void
    {
        if (!isset($properties[$key]) || !is_array($properties[$key])) {
            return;
        }

        $stringArray = $this->ensureStringArray($properties[$key]);
        if (null === $stringArray) {
            return;
        }

        match ($setter) {
            'setAllowedUsers' => $commandLimit->setAllowedUsers($stringArray),
            'setAllowedUserTags' => $commandLimit->setAllowedUserTags($stringArray),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function updateBoolProperty(CommandLimit $commandLimit, array $properties, string $key, string $setter): void
    {
        if (isset($properties[$key]) && is_bool($properties[$key]) && 'setIsEnabled' === $setter) {
            $commandLimit->setIsEnabled($properties[$key]);
        }
    }

    /**
     * @param array<mixed, mixed> $array
     * @return array<int, string>|null
     */
    private function ensureStringArray(array $array): ?array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (!is_int($key) || !is_string($value)) {
                return null;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private function setStartTime(CommandLimit $commandLimit, \DateTimeInterface $startTime): void
    {
        $immutableStartTime = !$startTime instanceof \DateTimeImmutable
            ? \DateTimeImmutable::createFromInterface($startTime)
            : $startTime;
        $commandLimit->setStartTime($immutableStartTime);
    }

    private function setEndTime(CommandLimit $commandLimit, \DateTimeInterface $endTime): void
    {
        $immutableEndTime = !$endTime instanceof \DateTimeImmutable
            ? \DateTimeImmutable::createFromInterface($endTime)
            : $endTime;
        $commandLimit->setEndTime($immutableEndTime);
    }

    /**
     * 删除口令限制
     */
    public function deleteCommandLimit(string $commandLimitId): bool
    {
        $commandLimit = $this->commandLimitRepository->find($commandLimitId);
        if (null === $commandLimit) {
            return false;
        }

        $this->entityManager->remove($commandLimit);
        $this->entityManager->flush();

        return true;
    }

    /**
     * 获取口令配置详情
     *
     * @return array<string, mixed>|null
     */
    public function getCommandConfigDetail(string $id): ?array
    {
        $commandConfig = $this->commandConfigRepository->find($id);
        if (null === $commandConfig) {
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
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommandConfigList(): array
    {
        $configs = $this->commandConfigRepository->findAll();
        $result = [];

        foreach ($configs as $config) {
            $configId = $config->getId();
            if (null === $configId) {
                continue;
            }
            $usageStats = $this->commandConfigRepository->getUsageStats((string) $configId);
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
        if (null === $commandLimit) {
            throw new CommandConfigurationException('限制配置不存在');
        }

        $commandLimit->setIsEnabled(!$commandLimit->isEnabled());

        $this->entityManager->persist($commandLimit);
        $this->entityManager->flush();

        return $commandLimit;
    }
}
