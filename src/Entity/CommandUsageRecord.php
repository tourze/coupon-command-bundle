<?php

namespace Tourze\CouponCommandBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Repository\CommandUsageRecordRepository;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

#[ORM\Entity(repositoryClass: CommandUsageRecordRepository::class)]
#[ORM\Table(name: 'coupon_command_usage_record', options: ['comment' => '优惠券口令使用记录'])]
class CommandUsageRecord implements ApiArrayInterface, \Stringable
{
    use CreateTimeAware;
    use CreatedByAware;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: CommandConfig::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CommandConfig $commandConfig = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: false, options: ['comment' => '使用用户ID'])]
    private ?string $userId = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '使用的口令内容'])]
    private ?string $commandText = null;

    #[ORM\Column(type: Types::STRING, length: 64, nullable: true, options: ['comment' => '获得的优惠券ID'])]
    private ?string $couponId = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '是否使用成功', 'default' => false])]
    private bool $isSuccess = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '失败原因'])]
    private ?string $failureReason = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '额外信息'])]
    private ?array $extraData = null;


    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    public function getId(): ?string
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

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCommandText(): ?string
    {
        return $this->commandText;
    }

    public function setCommandText(?string $commandText): self
    {
        $this->commandText = $commandText;

        return $this;
    }

    public function getCouponId(): ?string
    {
        return $this->couponId;
    }

    public function setCouponId(?string $couponId): self
    {
        $this->couponId = $couponId;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function setIsSuccess(bool $isSuccess): self
    {
        $this->isSuccess = $isSuccess;

        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): self
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setExtraData(?array $extraData): self
    {
        $this->extraData = $extraData;

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
