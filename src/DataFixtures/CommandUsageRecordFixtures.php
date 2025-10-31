<?php

namespace Tourze\CouponCommandBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;

#[When(env: 'test')]
#[When(env: 'dev')]
class CommandUsageRecordFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const USAGE_RECORD_SUCCESS_1 = 'usage-record-success-1';
    public const USAGE_RECORD_SUCCESS_2 = 'usage-record-success-2';
    public const USAGE_RECORD_FAILED_1 = 'usage-record-failed-1';
    public const USAGE_RECORD_FAILED_2 = 'usage-record-failed-2';

    public function load(ObjectManager $manager): void
    {
        $commandConfig1 = $this->getReference(CommandConfigFixtures::COMMAND_CONFIG_DISCOUNT_20, CommandConfig::class);
        $commandConfig2 = $this->getReference(CommandConfigFixtures::COMMAND_CONFIG_DISCOUNT_50, CommandConfig::class);

        $record1 = new CommandUsageRecord();
        $record1->setCommandConfig($commandConfig1);
        $record1->setUserId('user123');
        $record1->setCommandText('SAVE20NOW');
        $record1->setCouponId('coupon_12345');
        $record1->setIsSuccess(true);
        $record1->setExtraData(['ip' => '192.168.1.100', 'userAgent' => 'Mozilla/5.0']);
        $manager->persist($record1);

        $record2 = new CommandUsageRecord();
        $record2->setCommandConfig($commandConfig2);
        $record2->setUserId('user456');
        $record2->setCommandText('TECH50OFF');
        $record2->setCouponId('coupon_67890');
        $record2->setIsSuccess(true);
        $record2->setExtraData(['ip' => '192.168.1.101', 'userAgent' => 'Chrome/95.0']);
        $manager->persist($record2);

        $record3 = new CommandUsageRecord();
        $record3->setCommandConfig($commandConfig1);
        $record3->setUserId('user789');
        $record3->setCommandText('SAVE20NOW');
        $record3->setIsSuccess(false);
        $record3->setFailureReason('用户已达使用限制');
        $record3->setExtraData(['ip' => '192.168.1.102', 'attempt' => 2]);
        $manager->persist($record3);

        $record4 = new CommandUsageRecord();
        $record4->setCommandConfig($commandConfig2);
        $record4->setUserId('user999');
        $record4->setCommandText('TECH50OFF');
        $record4->setIsSuccess(false);
        $record4->setFailureReason('口令已过期');
        $record4->setExtraData(['ip' => '192.168.1.103', 'timestamp' => time()]);
        $manager->persist($record4);

        $manager->flush();

        $this->addReference(self::USAGE_RECORD_SUCCESS_1, $record1);
        $this->addReference(self::USAGE_RECORD_SUCCESS_2, $record2);
        $this->addReference(self::USAGE_RECORD_FAILED_1, $record3);
        $this->addReference(self::USAGE_RECORD_FAILED_2, $record4);
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
