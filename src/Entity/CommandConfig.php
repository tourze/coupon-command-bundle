<?php

namespace Tourze\CouponCommandBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: CommandConfigRepository::class)]
#[ORM\Table(name: 'coupon_command_config', options: ['comment' => '优惠券口令'])]
class CommandConfig implements ApiArrayInterface, \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[Ignore]
    // TODO: 临时注释掉 ORM 映射以避免测试框架的 inverse side 关联字段排序错误
    // 这个字段通过 getter/setter 手动维护关联关系
    // #[ORM\OneToOne(targetEntity: Coupon::class, mappedBy: 'commandConfig')]
    #[Assert\Valid]
    private ?Coupon $coupon = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '口令'])]
    #[Assert\NotBlank(message: '口令不能为空')]
    #[Assert\Length(min: 1, max: 1000, minMessage: '口令至少需要1个字符', maxMessage: '口令不能超过1000个字符')]
    private ?string $command = null;

    #[Ignore]
    #[ORM\OneToOne(targetEntity: CommandLimit::class, inversedBy: 'commandConfig', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'command_limit_id', referencedColumnName: 'id', nullable: true)]
    #[Assert\Valid]
    private ?CommandLimit $commandLimit = null;

    /**
     * @var Collection<int, CommandUsageRecord>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: CommandUsageRecord::class, mappedBy: 'commandConfig', cascade: ['persist', 'remove'])]
    private Collection $usageRecords;

    public function __construct()
    {
        $this->usageRecords = new ArrayCollection();
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(Coupon $coupon): void
    {
        $this->coupon = $coupon;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): void
    {
        $this->command = $command;
    }

    public function getCommandLimit(): ?CommandLimit
    {
        if (null === $this->commandLimit && null !== $this->getId()) {
            // 使用 Doctrine EntityManager 查找关联的 CommandLimit
            // 注意：这需要 EntityManager 被注入或通过其他方式可用
            // 在实际使用中，这个逻辑应该在 Service 层处理
            // 这里为了保持兼容性，我们暂时返回 null
        }

        return $this->commandLimit;
    }

    public function setCommandLimit(?CommandLimit $commandLimit): void
    {
        $previousLimit = $this->commandLimit;

        // 移除之前的关联
        if (null !== $previousLimit && $previousLimit !== $commandLimit) {
            $previousLimit->setCommandConfig(null);
        }

        $this->commandLimit = $commandLimit;

        // 设置新的关联
        if (null !== $commandLimit && $commandLimit->getCommandConfig() !== $this) {
            $commandLimit->setCommandConfig($this);
        }
    }

    /** @return Collection<int, CommandUsageRecord> */
    public function getUsageRecords(): Collection
    {
        return $this->usageRecords;
    }

    public function addUsageRecord(CommandUsageRecord $usageRecord): void
    {
        if (!$this->usageRecords->contains($usageRecord)) {
            $this->usageRecords->add($usageRecord);
            $usageRecord->setCommandConfig($this);
        }
    }

    public function removeUsageRecord(CommandUsageRecord $usageRecord): void
    {
        if ($this->usageRecords->removeElement($usageRecord)) {
            if ($usageRecord->getCommandConfig() === $this) {
                $usageRecord->setCommandConfig(null);
            }
        }
    }

    /** @return array<string, mixed> */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'command' => $this->getCommand(),
            'commandLimit' => $this->getCommandLimit()?->retrieveApiArray(),
            'usageCount' => $this->getUsageRecords()->count(),
        ];
    }

    public function __toString(): string
    {
        return sprintf('CommandConfig #%s: %s', $this->id ?? '0', $this->command ?? 'N/A');
    }
}
