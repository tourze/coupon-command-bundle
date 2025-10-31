<?php

namespace Tourze\CouponCommandBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('优惠券口令')) {
            $item->addChild('优惠券口令');
        }

        $couponCommandMenu = $item->getChild('优惠券口令');
        if (null === $couponCommandMenu) {
            return;
        }

        $couponCommandMenu->addChild('口令管理')
            ->setUri($this->linkGenerator->getCurdListPage(CommandConfig::class))
            ->setAttribute('icon', 'fas fa-ticket-alt')
        ;

        $couponCommandMenu->addChild('使用限制')
            ->setUri($this->linkGenerator->getCurdListPage(CommandLimit::class))
            ->setAttribute('icon', 'fas fa-shield-alt')
        ;

        $couponCommandMenu->addChild('使用记录')
            ->setUri($this->linkGenerator->getCurdListPage(CommandUsageRecord::class))
            ->setAttribute('icon', 'fas fa-history')
        ;
    }
}
