<?php

namespace Tourze\CouponCommandBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CouponCommandBundle\Repository\CommandConfigRepository;
use Tourze\CouponCoreBundle\Entity\Coupon;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: CommandConfigRepository::class)]
#[ORM\Table(name: 'coupon_command_config', options: ['comment' => '优惠券口令'])]
class CommandConfig implements ApiArrayInterface, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[Ignore]
    #[ORM\OneToOne(targetEntity: Coupon::class, inversedBy: 'commandConfig', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Coupon $coupon = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '口令'])]
    private ?string $command = null;

    #[Ignore]
    #[ORM\OneToOne(targetEntity: CommandLimit::class, mappedBy: 'commandConfig', cascade: ['persist', 'remove'])]
    private ?CommandLimit $commandLimit = null;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: CommandUsageRecord::class, mappedBy: 'commandConfig', cascade: ['persist', 'remove'])]
    private Collection $usageRecords;


    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    public function __construct()
    {
        $this->usageRecords = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(Coupon $coupon): self
    {
        $this->coupon = $coupon;

        return $this;
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
        return $this->commandLimit;
    }

    public function setCommandLimit(?CommandLimit $commandLimit): self
    {
        $this->commandLimit = $commandLimit;

        if ($commandLimit !== null) {
            $commandLimit->setCommandConfig($this);
        }

        return $this;
    }

    public function getUsageRecords(): Collection
    {
        return $this->usageRecords;
    }

    public function addUsageRecord(CommandUsageRecord $usageRecord): self
    {
        if (!$this->usageRecords->contains($usageRecord)) {
            $this->usageRecords[] = $usageRecord;
            $usageRecord->setCommandConfig($this);
        }

        return $this;
    }

    public function removeUsageRecord(CommandUsageRecord $usageRecord): self
    {
        if ($this->usageRecords->removeElement($usageRecord)) {
            if ($usageRecord->getCommandConfig() === $this) {
                $usageRecord->setCommandConfig(null);
            }
        }

        return $this;
    }public function retrieveApiArray(): array
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
