<?php

namespace Tourze\CouponCommandBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCoreBundle\DataFixtures\CouponFixtures;
use Tourze\CouponCoreBundle\Entity\Coupon;

#[When(env: 'test')]
#[When(env: 'dev')]
class CommandConfigFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const COMMAND_CONFIG_DISCOUNT_20 = 'command-config-discount-20';
    public const COMMAND_CONFIG_DISCOUNT_50 = 'command-config-discount-50';
    public const COMMAND_CONFIG_PERCENT_10 = 'command-config-percent-10';
    public const COMMAND_CONFIG_FREE_SHIPPING = 'command-config-free-shipping';

    public function load(ObjectManager $manager): void
    {
        $discount20Coupon = $this->getReference(CouponFixtures::COUPON_BASIC_DISCOUNT, Coupon::class);
        $discount50Coupon = $this->getReference(CouponFixtures::COUPON_SHORT_TERM, Coupon::class);
        $percent10Coupon = $this->getReference(CouponFixtures::COUPON_LONG_TERM, Coupon::class);
        $freeShippingCoupon = $this->getReference(CouponFixtures::COUPON_INACTIVE, Coupon::class);

        $config1 = new CommandConfig();
        $config1->setCoupon($discount20Coupon);
        $config1->setCommand('SAVE20NOW');
        $manager->persist($config1);

        $config2 = new CommandConfig();
        $config2->setCoupon($discount50Coupon);
        $config2->setCommand('TECH50OFF');
        $manager->persist($config2);

        $config3 = new CommandConfig();
        $config3->setCoupon($percent10Coupon);
        $config3->setCommand('DINE10PERCENT');
        $manager->persist($config3);

        $config4 = new CommandConfig();
        $config4->setCoupon($freeShippingCoupon);
        $config4->setCommand('FREESHIP2024');
        $manager->persist($config4);

        $manager->flush();

        $this->addReference(self::COMMAND_CONFIG_DISCOUNT_20, $config1);
        $this->addReference(self::COMMAND_CONFIG_DISCOUNT_50, $config2);
        $this->addReference(self::COMMAND_CONFIG_PERCENT_10, $config3);
        $this->addReference(self::COMMAND_CONFIG_FREE_SHIPPING, $config4);
    }

    public function getDependencies(): array
    {
        return [
            CouponFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['coupon-command', 'test'];
    }
}
