<?php

declare(strict_types=1);

namespace Tourze\CouponCommandBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CouponCommandBundle\CouponCommandBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(CouponCommandBundle::class)]
#[RunTestsInSeparateProcesses]
final class CouponCommandBundleTest extends AbstractBundleTestCase
{
}
