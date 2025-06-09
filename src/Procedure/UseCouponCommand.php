<?php

namespace Tourze\CouponCommandBundle\Procedure;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Tourze\CouponCommandBundle\Service\CommandValidationService;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodExpose(method: 'UseCouponCommand')]
#[MethodDoc(summary: '使用优惠券口令', description: '使用指定口令领取优惠券')]
class UseCouponCommand extends BaseProcedure
{
    #[MethodParam(description: '口令内容')]
    #[NotBlank(message: '口令不能为空')]
    #[Type(type: 'string', message: '口令必须是字符串')]
    public string $command;

    #[MethodParam(description: '用户ID')]
    #[NotBlank(message: '用户ID不能为空')]
    #[Type(type: 'string', message: '用户ID必须是字符串')]
    public string $userId;

    public function __construct(private readonly CommandValidationService $commandValidationService)
    {
    }

    public function execute(): array
    {
        return $this->commandValidationService->useCommand(
            $this->command,
            $this->userId
        );
    }

    public static function getMockResult(): ?array
    {
        return [
            'success' => true,
            'couponId' => '1234567890',
            'message' => '优惠券领取成功',
        ];
    }
}
