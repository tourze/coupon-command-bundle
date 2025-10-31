<?php

namespace Tourze\CouponCommandBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Repository\CommandUsageRecordRepository;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

/**
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: CommandUsageRecordRepository::class)]
#[ORM\Table(name: 'coupon_command_usage_record', options: ['comment' => '优惠券口令使用记录'])]
class CommandUsageRecord implements ApiArrayInterface, \Stringable
{
    use SnowflakeKeyAware;
    use CreateTimeAware;
    use CreatedByAware;
    use CreatedFromIpAware;

    #[ORM\ManyToOne(targetEntity: CommandConfig::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CommandConfig $commandConfig = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '使用用户ID'])]
    #[Assert\NotBlank(message: '用户ID不能为空')]
    #[Assert\Length(max: 64, maxMessage: '用户ID不能超过64个字符')]
    private ?string $userId = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '使用的口令内容'])]
    #[Assert\NotBlank(message: '口令内容不能为空')]
    #[Assert\Length(min: 1, max: 1000, minMessage: '口令内容至少需要1个字符', maxMessage: '口令内容不能超过1000个字符')]
    private ?string $commandText = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '获得的优惠券ID'])]
    #[Assert\Length(max: 64, maxMessage: '优惠券ID不能超过64个字符')]
    private ?string $couponId = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否使用成功', 'default' => false])]
    #[Assert\Type(type: 'bool', message: '使用状态必须是布尔值')]
    private bool $isSuccess = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '失败原因'])]
    #[Assert\Length(max: 255, maxMessage: '失败原因不能超过255个字符')]
    private ?string $failureReason = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '额外信息'])]
    #[Assert\Type(type: 'array', message: '额外信息必须是数组')]
    private ?array $extraData = null;

    public function getCommandConfig(): ?CommandConfig
    {
        return $this->commandConfig;
    }

    public function setCommandConfig(?CommandConfig $commandConfig): void
    {
        $this->commandConfig = $commandConfig;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getCommandText(): ?string
    {
        return $this->commandText;
    }

    public function setCommandText(?string $commandText): void
    {
        $this->commandText = $commandText;
    }

    public function getCouponId(): ?string
    {
        return $this->couponId;
    }

    public function setCouponId(?string $couponId): void
    {
        $this->couponId = $couponId;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function setIsSuccess(bool $isSuccess): void
    {
        $this->isSuccess = $isSuccess;
    }

    public function setSuccess(bool $isSuccess): void
    {
        $this->isSuccess = $isSuccess;
    }

    public function setCreateTime(?\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): void
    {
        $this->failureReason = $failureReason;
    }

    /** @return array<string, mixed>|null */
    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    /**
     * 为 EasyAdmin 提供字符串表示
     */
    public function getExtraDataDisplay(): string
    {
        if (!is_array($this->extraData)) {
            return '';
        }

        $encoded = json_encode($this->extraData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return false !== $encoded ? $encoded : '';
    }

    /** @param array<string, mixed>|null $extraData */
    public function setExtraData(?array $extraData): void
    {
        $this->extraData = $extraData;
    }

    /** @return array<string, mixed> */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'userId' => $this->getUserId(),
            'commandText' => $this->getCommandText(),
            'couponId' => $this->getCouponId(),
            'isSuccess' => $this->isSuccess(),
            'failureReason' => $this->getFailureReason(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'extraData' => $this->getExtraData(),
        ];
    }

    public function __toString(): string
    {
        return sprintf('CommandUsageRecord #%s: User %s used command %s',
            $this->id ?? '0',
            $this->userId ?? 'N/A',
            $this->commandText ?? 'N/A'
        );
    }
}
