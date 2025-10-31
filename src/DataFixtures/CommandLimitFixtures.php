<?php

namespace Tourze\CouponCommandBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandLimit;

#[When(env: 'test')]
#[When(env: 'dev')]
class CommandLimitFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const COMMAND_LIMIT_DISCOUNT_20 = 'command-limit-discount-20';
    public const COMMAND_LIMIT_DISCOUNT_50 = 'command-limit-discount-50';
    public const COMMAND_LIMIT_PERCENT_10 = 'command-limit-percent-10';
    public const COMMAND_LIMIT_FREE_SHIPPING = 'command-limit-free-shipping';

    public function load(ObjectManager $manager): void
    {
        $commandConfig1 = $this->getReference(CommandConfigFixtures::COMMAND_CONFIG_DISCOUNT_20, CommandConfig::class);
        $commandConfig2 = $this->getReference(CommandConfigFixtures::COMMAND_CONFIG_DISCOUNT_50, CommandConfig::class);
        $commandConfig3 = $this->getReference(CommandConfigFixtures::COMMAND_CONFIG_PERCENT_10, CommandConfig::class);
        $commandConfig4 = $this->getReference(CommandConfigFixtures::COMMAND_CONFIG_FREE_SHIPPING, CommandConfig::class);

        $limit1 = new CommandLimit();
        $limit1->setCommandConfig($commandConfig1);
        $limit1->setMaxUsagePerUser(1);
        $limit1->setMaxTotalUsage(1000);
        $limit1->setCurrentUsage(0);
        $limit1->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $limit1->setEndTime(new \DateTimeImmutable('2024-12-31'));
        $limit1->setIsEnabled(true);
        $manager->persist($limit1);

        $limit2 = new CommandLimit();
        $limit2->setCommandConfig($commandConfig2);
        $limit2->setMaxUsagePerUser(1);
        $limit2->setMaxTotalUsage(100);
        $limit2->setCurrentUsage(5);
        $limit2->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $limit2->setEndTime(new \DateTimeImmutable('2024-06-30'));
        $limit2->setAllowedUsers(['user123', 'user456']);
        $limit2->setIsEnabled(true);
        $manager->persist($limit2);

        $limit3 = new CommandLimit();
        $limit3->setCommandConfig($commandConfig3);
        $limit3->setMaxUsagePerUser(3);
        $limit3->setMaxTotalUsage(null);
        $limit3->setCurrentUsage(50);
        $limit3->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $limit3->setEndTime(new \DateTimeImmutable('2024-12-31'));
        $limit3->setAllowedUserTags(['vip', 'restaurant']);
        $limit3->setIsEnabled(true);
        $manager->persist($limit3);

        $limit4 = new CommandLimit();
        $limit4->setCommandConfig($commandConfig4);
        $limit4->setMaxUsagePerUser(1);
        $limit4->setMaxTotalUsage(500);
        $limit4->setCurrentUsage(120);
        $limit4->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $limit4->setEndTime(new \DateTimeImmutable('2024-12-31'));
        $limit4->setIsEnabled(true);
        $manager->persist($limit4);

        $manager->flush();

        $this->addReference(self::COMMAND_LIMIT_DISCOUNT_20, $limit1);
        $this->addReference(self::COMMAND_LIMIT_DISCOUNT_50, $limit2);
        $this->addReference(self::COMMAND_LIMIT_PERCENT_10, $limit3);
        $this->addReference(self::COMMAND_LIMIT_FREE_SHIPPING, $limit4);
    }

    public function getDependencies(): array
    {
        return [
            CommandConfigFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['coupon-command', 'test'];
    }
}
