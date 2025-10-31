<?php

namespace Tourze\CouponCommandBundle\Procedure;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Tourze\CouponCommandBundle\Service\CommandValidationService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodExpose(method: 'ValidateCouponCommand')]
#[MethodTag(name: '优惠券')]
#[MethodDoc(summary: '验证优惠券口令', description: '验证指定口令的有效性，不实际使用口令')]
class ValidateCouponCommand extends BaseProcedure
{
    #[MethodParam(description: '口令内容')]
    #[NotBlank(message: '口令不能为空')]
    #[Type(type: 'string', message: '口令必须是字符串')]
    public string $command;

    #[MethodParam(description: '用户ID（可选，用于检查用户相关限制）')]
    #[Type(type: 'string', message: '用户ID必须是字符串')]
    public ?string $userId = null;

    public function __construct(private readonly CommandValidationService $commandValidationService)
    {
    }

    /** @return array<string, mixed> */
    public function execute(): array
    {
        return $this->commandValidationService->validateCommand(
            $this->command,
            $this->userId
        );
    }

    /** @return array<string, mixed>|null */
    public static function getMockResult(): ?array
    {
        return [
            'valid' => true,
            'reason' => null,
            'couponInfo' => [
                'id' => '1234567890',
                'name' => '新用户优惠券',
                'type' => 'discount',
                'amount' => 100,
                'description' => '新用户专享优惠券',
                'validUntil' => '2024-12-31 23:59:59',
            ],
            'commandConfig' => [
                'id' => '9876543210',
                'command' => 'NEWUSER2024',
                'commandLimit' => [
                    'maxUsagePerUser' => 1,
                    'maxTotalUsage' => 1000,
                    'currentUsage' => 50,
                ],
            ],
        ];
    }
}
