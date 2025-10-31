# CouponCommandBundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/coupon-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-command-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/coupon-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-command-bundle)
[![License](https://img.shields.io/packagist/l/tourze/coupon-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-command-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/test.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/coupon-command-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/coupon-command-bundle)

A Symfony bundle for coupon command code system that provides 
command-based coupon distribution and management functionality.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Concepts](#core-concepts)
- [JsonRPC Interface](#jsonrpc-interface)
- [Admin Interface](#admin-interface)
- [Architecture](#architecture)
- [Usage Examples](#usage-examples)
- [Advanced Usage](#advanced-usage)
- [Service Configuration](#service-configuration)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)
- [Changelog](#changelog)

## Features

- **Command Management**: Create, edit, and delete coupon command codes
- **Usage Restrictions**: Multi-dimensional usage restrictions (time, count, 
user groups, etc.)
- **JsonRPC Interface**: Standard JSON-RPC 2.0 API endpoints
- **Admin Interface**: Complete management interface based on EasyAdmin
- **Usage Records**: Complete command usage tracking and statistics
- **Flexible Restrictions**: Time windows, usage limits, and user targeting
- **User Group Targeting**: Allow specific users or user tags to use commands

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- tourze/coupon-core-bundle
- tourze/json-rpc-core

## Installation

### Install via Composer

```bash
composer require tourze/coupon-command-bundle
```

### Enable the Bundle

Add to `config/bundles.php`:

```php
return [
    // ...
    Tourze\CouponCommandBundle\CouponCommandBundle::class => ['all' => true],
];
```

### Database Migration

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Quick Start

```php
use Tourze\CouponCommandBundle\Service\CommandValidationService;
use Tourze\CouponCommandBundle\Service\CommandManagementService;

// Validate command
$validationService = $container->get(CommandValidationService::class);
$result = $validationService->validateCommand('NEWUSER2024', 'user123');

if ($result['valid']) {
    // Command is valid, can be used
    $useResult = $validationService->useCommand('NEWUSER2024', 'user123');
    
    if ($useResult['success']) {
        echo "Coupon claimed successfully, ID: " . $useResult['couponId'];
    }
}
```

## Core Concepts

### Restriction Types

1. **Time Restrictions**: Set valid time range for commands
2. **Count Restrictions**: Total usage count and per-user usage limits
3. **User Restrictions**: Specify allowed user groups for command usage
4. **Status Control**: Enable/disable command restrictions

## JsonRPC Interface

### Validate Command - ValidateCouponCommand

Validates command validity without actually using the command.

**Request Parameters:**
```json
{
    "command": "string",     // Command content
    "userId": "string"       // User ID (optional)
}
```

**Response Example:**
```json
{
    "valid": true,
    "couponInfo": {
        "id": "1234567890",
        "name": "New User Coupon",
        "type": "discount",
        "amount": 100
    },
    "commandConfig": {
        "id": "9876543210",
        "command": "NEWUSER2024"
    }
}
```

### Use Command - UseCouponCommand

Use command to claim coupon.

**Request Parameters:**
```json
{
    "command": "string",     // Command content
    "userId": "string"       // User ID (required)
}
```

**Response Example:**
```json
{
    "success": true,
    "couponId": "1234567890",
    "message": "Coupon claimed successfully"
}
```

## Admin Interface

The bundle provides complete EasyAdmin management interface:

1. **Command Configuration Management** - `CommandConfigCrudController`
    - Create and edit commands
    - Link to coupons
    - View usage statistics

2. **Restriction Configuration Management** - `CommandLimitCrudController`
    - Set usage restrictions
    - Configure time windows
    - Manage user group restrictions

3. **Usage Record Viewing** - `CommandUsageRecordCrudController`
    - View detailed usage records
    - Success/failure statistics
    - User usage tracking

## Architecture

### Entity Relationships

- **CommandConfig**: Command configuration, linked to specific coupons
- **CommandLimit**: Command usage restriction configuration
- **CommandUsageRecord**: Command usage tracking records

### Service Layer

- **CommandValidationService**: Command validation and execution
- **CommandManagementService**: Command management and statistics

### Core Features

- **Audit Trails**: All usage is tracked with IP, user, and timestamp 
information
- **Flexible Validation**: Time-based, count-based, and user-based 
restrictions
- **Admin Interface**: Complete CRUD operations via EasyAdmin controllers

## Usage Examples

### Basic Usage

```php
// Create command configuration
$managementService = $container->get(CommandManagementService::class);
$commandConfig = $managementService->createCommandConfig('SPRING2024', $coupon);

// Add usage restrictions
$managementService->addCommandLimit(
    $commandConfig->getId(),
    maxUsagePerUser: 1,        // 1 use per person
    maxTotalUsage: 1000,       // 1000 total uses
    startTime: new \DateTime('2024-03-01'),
    endTime: new \DateTime('2024-03-31')
);
```

## Advanced Usage

### User Group Restrictions

```php
// Create command with user group restrictions
$managementService->addCommandLimit(
    $commandConfigId,
    maxUsagePerUser: 3,
    allowedUsers: ['vip_user_1', 'vip_user_2'],  // Only allow VIP users
    allowedUserTags: ['premium', 'gold']         // Allow specific tagged users
);

// Get usage statistics
$stats = $managementService->getCommandConfigDetail($commandConfigId);
echo "Total usage: " . $stats['stats']['totalUsage'];
echo "Successful usage: " . $stats['stats']['successUsage'];
```

## Service Configuration

In `services.yaml`, the bundle automatically registers the following services:

- `CommandValidationService`: Command validation service
- `CommandManagementService`: Command management service
- JsonRPC methods: `ValidateCouponCommand`, `UseCouponCommand`

## Error Handling

### Common Error Messages

| Error Message | Description | Solution |
|---------------|-------------|----------|
| Command does not exist | Entered command is not configured | Check if command is correct or has been created |
| Coupon does not exist | Associated coupon has been deleted | Re-associate with a valid coupon |
| Command usage time is outside valid period | Outside time restrictions | Check time configuration |
| Command usage count has reached limit | Exceeded total count limit | Increase limit or create new command |
| You are not in the usage scope of this command | User not in allowed list | Add user to allowed list |
| You have reached the usage limit for this command | User personal count limit exceeded | Increase personal usage limit |

### Logging

All command usage is logged with detailed information:

- Usage timestamp
- User information
- Success/failure status
- Failure reason
- Additional context information

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/coupon-command-bundle/tests
```

Test coverage includes:
- Entity unit tests
- Service layer business logic tests
- Integration tests
- JsonRPC interface tests

## Contributing

Please see [CONTRIBUTING.md](../../CONTRIBUTING.md) for details on how to 
contribute to this project.

## Support

For issues or suggestions, please submit an Issue or Pull Request on the 
[main repository](https://github.com/tourze/php-monorepo).

## License

MIT License

## Changelog

### v0.0.1
- Initial version release
- Complete command system functionality
- JSON-RPC 2.0 interface support
- EasyAdmin management interface
- Complete unit test coverage