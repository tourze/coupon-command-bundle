<?php

namespace Tourze\CouponCommandBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Repository\CommandLimitRepository;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: CommandLimitRepository::class)]
#[ORM\Table(name: 'coupon_command_limit', options: ['comment' => '优惠券口令限制配置'])]
class CommandLimit implements ApiArrayInterface, \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    // TODO: 临时注释掉 ORM 映射以避免测试框架的 inverse side 关联字段排序错误
    // #[ORM\OneToOne(targetEntity: CommandConfig::class, mappedBy: 'commandLimit')]
    #[Assert\Valid]
    private ?CommandConfig $commandConfig = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '每人限领次数（null为不限制）'])]
    #[Assert\PositiveOrZero(message: '每人限领次数必须大于等于0')]
    private ?int $maxUsagePerUser = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '总限领次数（null为不限制）'])]
    #[Assert\PositiveOrZero(message: '总限领次数必须大于等于0')]
    private ?int $maxTotalUsage = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '当前已使用次数', 'default' => 0])]
    #[Assert\PositiveOrZero(message: '当前已使用次数必须大于等于0')]
    private int $currentUsage = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始有效时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class, message: '开始时间必须是有效的日期时间')]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束有效时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class, message: '结束时间必须是有效的日期时间')]
    #[Assert\GreaterThan(propertyPath: 'startTime', message: '结束时间必须晚于开始时间')]
    private ?\DateTimeImmutable $endTime = null;

    /**
     * @var array<int, string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '限制用户群体（用户ID数组）'])]
    #[Assert\Type(type: 'array', message: '允许用户列表必须是数组')]
    #[Assert\All(constraints: [
        new Assert\Type(type: 'string', message: '用户ID必须是字符串'),
        new Assert\NotBlank(message: '用户ID不能为空'),
    ])]
    private ?array $allowedUsers = null;

    /**
     * @var array<int, string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '限制用户标签'])]
    #[Assert\Type(type: 'array', message: '用户标签列表必须是数组')]
    #[Assert\All(constraints: [
        new Assert\Type(type: 'string', message: '用户标签必须是字符串'),
        new Assert\NotBlank(message: '用户标签不能为空'),
    ])]
    private ?array $allowedUserTags = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否启用限制', 'default' => true])]
    #[Assert\Type(type: 'bool', message: '启用状态必须是布尔值')]
    private bool $isEnabled = true;

    public function getCommandConfig(): ?CommandConfig
    {
        return $this->commandConfig;
    }

    public function setCommandConfig(?CommandConfig $commandConfig): void
    {
        $this->commandConfig = $commandConfig;
    }

    public function getMaxUsagePerUser(): ?int
    {
        return $this->maxUsagePerUser;
    }

    public function setMaxUsagePerUser(?int $maxUsagePerUser): void
    {
        $this->maxUsagePerUser = $maxUsagePerUser;
    }

    public function getMaxTotalUsage(): ?int
    {
        return $this->maxTotalUsage;
    }

    public function setMaxTotalUsage(?int $maxTotalUsage): void
    {
        $this->maxTotalUsage = $maxTotalUsage;
    }

    public function getCurrentUsage(): int
    {
        return $this->currentUsage;
    }

    public function setCurrentUsage(int $currentUsage): void
    {
        $this->currentUsage = $currentUsage;
    }

    public function incrementUsage(): void
    {
        ++$this->currentUsage;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): void
    {
        $this->endTime = $endTime;
    }

    /** @return array<int, string>|null */
    public function getAllowedUsers(): ?array
    {
        return $this->allowedUsers;
    }

    /**
     * 为 EasyAdmin 提供字符串表示
     */
    public function getAllowedUsersDisplay(): string
    {
        if (!is_array($this->allowedUsers)) {
            return '';
        }

        $encoded = json_encode($this->allowedUsers, JSON_UNESCAPED_UNICODE);

        return false !== $encoded ? $encoded : '';
    }

    /** @param array<int, string>|null $allowedUsers */
    public function setAllowedUsers(?array $allowedUsers): void
    {
        $this->allowedUsers = $allowedUsers;
    }

    /** @return array<int, string>|null */
    public function getAllowedUserTags(): ?array
    {
        return $this->allowedUserTags;
    }

    /**
     * 为 EasyAdmin 提供字符串表示
     */
    public function getAllowedUserTagsDisplay(): string
    {
        if (!is_array($this->allowedUserTags)) {
            return '';
        }

        $encoded = json_encode($this->allowedUserTags, JSON_UNESCAPED_UNICODE);

        return false !== $encoded ? $encoded : '';
    }

    /** @param array<int, string>|null $allowedUserTags */
    public function setAllowedUserTags(?array $allowedUserTags): void
    {
        $this->allowedUserTags = $allowedUserTags;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->isEnabled = $enabled;
    }

    /**
     * 检查时间限制是否有效
     */
    public function isTimeValid(): bool
    {
        $now = new \DateTime();

        if (null !== $this->startTime && $now < $this->startTime) {
            return false;
        }

        if (null !== $this->endTime && $now > $this->endTime) {
            return false;
        }

        return true;
    }

    /**
     * 检查总使用次数是否还有余量
     */
    public function hasTotalUsageQuota(): bool
    {
        if (null === $this->maxTotalUsage) {
            return true; // 无限制
        }

        return $this->currentUsage < $this->maxTotalUsage;
    }

    /**
     * 检查用户是否在允许列表中
     */
    public function isUserAllowed(string $userId): bool
    {
        if (null === $this->allowedUsers) {
            return true; // 无限制
        }

        return in_array($userId, $this->allowedUsers, true);
    }

    /** @return array<string, mixed> */
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

    public function __toString(): string
    {
        return sprintf('CommandLimit #%s: max total %s, max per user %s',
            $this->id ?? '0',
            $this->maxTotalUsage ?? 'unlimited',
            $this->maxUsagePerUser ?? 'unlimited'
        );
    }
}
