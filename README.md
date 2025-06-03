# CouponCommandBundle

优惠券口令系统Bundle，提供基于口令的优惠券发放和管理功能。

## 功能特性

- **口令管理**：创建、编辑、删除优惠券口令
- **使用限制**：支持多维度使用限制（时间、次数、用户群体等）
- **JsonRPC 接口**：标准的 JSON-RPC 2.0 API 接口
- **管理后台**：基于 EasyAdmin 的完整管理界面
- **使用记录**：完整的口令使用记录和统计

## 安装

### 通过 Composer 安装

```bash
composer require tourze/coupon-command-bundle
```

### 启用 Bundle

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    Tourze\CouponCommandBundle\CouponCommandBundle::class => ['all' => true],
];
```

### 数据库迁移

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## 核心概念

### 实体关系

- **CommandConfig**：口令配置，关联具体的优惠券
- **CommandLimit**：口令使用限制配置
- **CommandUsageRecord**：口令使用记录

### 限制类型

1. **时间限制**：设置口令有效时间范围
2. **次数限制**：总使用次数和用户单次使用次数限制
3. **用户限制**：指定允许使用口令的用户群体
4. **状态控制**：启用/禁用口令限制

## JsonRPC 接口

### 验证口令 - ValidateCouponCommand

验证口令有效性，不实际使用口令。

**请求参数：**
```json
{
    "command": "string",     // 口令内容
    "userId": "string"       // 用户ID（可选）
}
```

**响应示例：**
```json
{
    "valid": true,
    "couponInfo": {
        "id": "1234567890",
        "name": "新用户优惠券",
        "type": "discount",
        "amount": 100
    },
    "commandConfig": {
        "id": "9876543210",
        "command": "NEWUSER2024"
    }
}
```

### 使用口令 - UseCouponCommand

使用口令领取优惠券。

**请求参数：**
```json
{
    "command": "string",     // 口令内容
    "userId": "string"       // 用户ID（必填）
}
```

**响应示例：**
```json
{
    "success": true,
    "couponId": "1234567890",
    "message": "优惠券领取成功"
}
```

## 管理界面

Bundle 提供完整的 EasyAdmin 管理界面：

1. **口令配置管理** - `CommandConfigCrudController`
   - 创建、编辑口令
   - 关联优惠券
   - 查看使用统计

2. **限制配置管理** - `CommandLimitCrudController`
   - 设置使用限制
   - 时间窗口配置
   - 用户群体限制

3. **使用记录查看** - `CommandUsageRecordCrudController`
   - 查看详细使用记录
   - 成功/失败统计
   - 用户使用轨迹

## 使用示例

### 基本用法

```php
use Tourze\CouponCommandBundle\Service\CommandValidationService;
use Tourze\CouponCommandBundle\Service\CommandManagementService;

// 验证口令
$validationService = $container->get(CommandValidationService::class);
$result = $validationService->validateCommand('NEWUSER2024', 'user123');

if ($result['valid']) {
    // 口令有效，可以使用
    $useResult = $validationService->useCommand('NEWUSER2024', 'user123');
    
    if ($useResult['success']) {
        echo "优惠券领取成功，ID：" . $useResult['couponId'];
    }
}

// 创建口令配置
$managementService = $container->get(CommandManagementService::class);
$commandConfig = $managementService->createCommandConfig('SPRING2024', $coupon);

// 添加使用限制
$managementService->addCommandLimit(
    $commandConfig->getId(),
    maxUsagePerUser: 1,        // 每人限用1次
    maxTotalUsage: 1000,       // 总共1000次
    startTime: new \DateTime('2024-03-01'),
    endTime: new \DateTime('2024-03-31')
);
```

### 高级配置

```php
// 创建带用户群体限制的口令
$managementService->addCommandLimit(
    $commandConfigId,
    maxUsagePerUser: 3,
    allowedUsers: ['vip_user_1', 'vip_user_2'],  // 只允许VIP用户
    allowedUserTags: ['premium', 'gold']         // 允许特定标签用户
);

// 获取使用统计
$stats = $managementService->getCommandConfigDetail($commandConfigId);
echo "总使用次数：" . $stats['stats']['totalUsage'];
echo "成功次数：" . $stats['stats']['successUsage'];
```

## 服务配置

在 `services.yaml` 中，Bundle 会自动注册以下服务：

- `CommandValidationService`：口令验证服务
- `CommandManagementService`：口令管理服务
- JsonRPC 方法：`ValidateCouponCommand`、`UseCouponCommand`

## 错误处理

### 常见错误码

| 错误消息 | 说明 | 解决方案 |
|---------|------|---------|
| 口令不存在 | 输入的口令未配置 | 检查口令是否正确或已创建 |
| 优惠券不存在 | 关联的优惠券已删除 | 重新关联有效的优惠券 |
| 口令使用时间超出有效期 | 超出时间限制 | 检查时间配置 |
| 口令使用次数已达上限 | 超出总次数限制 | 增加限制次数或创建新口令 |
| 您不在此口令的使用范围内 | 用户不在允许列表 | 添加用户到允许列表 |
| 您已达到此口令的使用次数上限 | 用户个人次数限制 | 增加个人使用次数限制 |

### 日志记录

所有口令使用都会记录详细日志：

- 使用时间
- 用户信息
- 成功/失败状态
- 失败原因
- 额外上下文信息

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/coupon-command-bundle/tests
```

测试覆盖：
- 实体单元测试
- 服务层业务逻辑测试
- 集成测试
- JsonRPC 接口测试

## 依赖要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- tourze/coupon-core-bundle
- tourze/json-rpc-core

## 许可证

MIT License

## 支持

如有问题或建议，请提交 Issue 或 Pull Request。

## 更新日志

### v0.0.1
- 初始版本发布
- 完整的口令系统功能
- JsonRPC 2.0 接口支持
- EasyAdmin 管理界面
- 完整的单元测试覆盖
