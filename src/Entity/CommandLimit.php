<?php

namespace Tourze\CouponCommandBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Repository\CommandLimitRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;

#[ORM\Entity(repositoryClass: CommandLimitRepository::class)]
#[ORM\Table(name: 'coupon_command_limit', options: ['comment' => '优惠券口令限制配置'])]
class CommandLimit implements ApiArrayInterface
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: CommandConfig::class, inversedBy: 'commandLimit')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CommandConfig $commandConfig = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '每人限领次数（null为不限制）'])]
    private ?int $maxUsagePerUser = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '总限领次数（null为不限制）'])]
    private ?int $maxTotalUsage = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '当前已使用次数', 'default' => 0])]
    private int $currentUsage = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '开始有效时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '结束有效时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '限制用户群体（用户ID数组）'])]
    private ?array $allowedUsers = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '限制用户标签'])]
    private ?array $allowedUserTags = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用限制', 'default' => true])]
    private bool $isEnabled = true;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    #[IndexColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]#[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]public function getId(): ?string
    {
        return $this->id;
    }

    public function getCommandConfig(): ?CommandConfig
    {
        return $this->commandConfig;
    }

    public function setCommandConfig(?CommandConfig $commandConfig): self
    {
        $this->commandConfig = $commandConfig;

        return $this;
    }

    public function getMaxUsagePerUser(): ?int
    {
        return $this->maxUsagePerUser;
    }

    public function setMaxUsagePerUser(?int $maxUsagePerUser): self
    {
        $this->maxUsagePerUser = $maxUsagePerUser;

        return $this;
    }

    public function getMaxTotalUsage(): ?int
    {
        return $this->maxTotalUsage;
    }

    public function setMaxTotalUsage(?int $maxTotalUsage): self
    {
        $this->maxTotalUsage = $maxTotalUsage;

        return $this;
    }

    public function getCurrentUsage(): int
    {
        return $this->currentUsage;
    }

    public function setCurrentUsage(int $currentUsage): self
    {
        $this->currentUsage = $currentUsage;

        return $this;
    }

    public function incrementUsage(): self
    {
        $this->currentUsage++;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getAllowedUsers(): ?array
    {
        return $this->allowedUsers;
    }

    public function setAllowedUsers(?array $allowedUsers): self
    {
        $this->allowedUsers = $allowedUsers;

        return $this;
    }

    public function getAllowedUserTags(): ?array
    {
        return $this->allowedUserTags;
    }

    public function setAllowedUserTags(?array $allowedUserTags): self
    {
        $this->allowedUserTags = $allowedUserTags;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }/**
     * 检查时间限制是否有效
     */
    public function isTimeValid(): bool
    {
        $now = new \DateTime();

        if ($this->startTime && $now < $this->startTime) {
            return false;
        }

        if ($this->endTime && $now > $this->endTime) {
            return false;
        }

        return true;
    }

    /**
     * 检查总使用次数是否还有余量
     */
    public function hasTotalUsageQuota(): bool
    {
        if ($this->maxTotalUsage === null) {
            return true; // 无限制
        }

        return $this->currentUsage < $this->maxTotalUsage;
    }

    /**
     * 检查用户是否在允许列表中
     */
    public function isUserAllowed(string $userId): bool
    {
        if ($this->allowedUsers === null) {
            return true; // 无限制
        }

        return in_array($userId, $this->allowedUsers);
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'maxUsagePerUser' => $this->getMaxUsagePerUser(),
            'maxTotalUsage' => $this->getMaxTotalUsage(),
            'currentUsage' => $this->getCurrentUsage(),
            'startTime' => $this->getStartTime()?->format('Y-m-d H:i:s'),
            'endTime' => $this->getEndTime()?->format('Y-m-d H:i:s'),
            'allowedUsers' => $this->getAllowedUsers(),
            'allowedUserTags' => $this->getAllowedUserTags(),
            'isEnabled' => $this->isEnabled(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }
}
