<?php

namespace Tourze\CouponCommandBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\CouponCommandBundle\Entity\CommandLimit;

/**
 * @extends AbstractCrudController<CommandLimit>
 */
#[AdminCrud(
    routePath: '/coupon-command/limit',
    routeName: 'coupon_command_limit',
)]
final class CommandLimitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandLimit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('口令限制配置')
            ->setEntityLabelInPlural('口令限制配置')
            ->setPageTitle('index', '口令限制配置管理')
            ->setPageTitle('new', '创建限制配置')
            ->setPageTitle('edit', '编辑限制配置')
            ->setPageTitle('detail', '限制配置详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            })
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 根据页面类型配置不同的字段集
        match ($pageName) {
            Crud::PAGE_INDEX => yield from $this->getIndexFields(),
            Crud::PAGE_DETAIL => yield from $this->getDetailFields(),
            Crud::PAGE_NEW, Crud::PAGE_EDIT => yield from $this->getFormFields(),
            default => yield from $this->getIndexFields(),
        };
    }

    /** @return iterable<FieldInterface> */
    private function getIndexFields(): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield IntegerField::new('maxUsagePerUser', '每人限领次数')->setColumns(3);
        yield IntegerField::new('maxTotalUsage', '总限领次数')->setColumns(3);
        yield IntegerField::new('currentUsage', '当前已使用次数')->setColumns(3);
        yield DateTimeField::new('startTime', '开始有效时间')->setColumns(6)->setFormat('yyyy-MM-dd HH:mm:ss');
        yield DateTimeField::new('endTime', '结束有效时间')->setColumns(6)->setFormat('yyyy-MM-dd HH:mm:ss');
        yield BooleanField::new('isEnabled', '是否启用')->setColumns(3)->renderAsSwitch(false);
        yield DateTimeField::new('createTime', '创建时间')->setColumns(3)->setFormat('yyyy-MM-dd HH:mm:ss');
        yield DateTimeField::new('updateTime', '更新时间')->setColumns(3)->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    /** @return iterable<FieldInterface> */
    private function getFormFields(): iterable
    {
        yield IntegerField::new('maxUsagePerUser', '每人限领次数')
            ->setColumns(3)
            ->setHelp('留空表示不限制')
        ;
        yield IntegerField::new('maxTotalUsage', '总限领次数')
            ->setColumns(3)
            ->setHelp('留空表示不限制')
        ;
        yield DateTimeField::new('startTime', '开始有效时间')
            ->setColumns(6)
            ->setHelp('留空表示立即生效')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield DateTimeField::new('endTime', '结束有效时间')
            ->setColumns(6)
            ->setHelp('留空表示永久有效')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield BooleanField::new('isEnabled', '是否启用')
            ->setColumns(3)
            ->renderAsSwitch(true)
        ;
    }

    /** @return iterable<FieldInterface> */
    private function getDetailFields(): iterable
    {
        // 显示所有字段，包括JSON字段
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield IntegerField::new('maxUsagePerUser', '每人限领次数')->setColumns(3);
        yield IntegerField::new('maxTotalUsage', '总限领次数')->setColumns(3);
        yield IntegerField::new('currentUsage', '当前已使用次数')->setColumns(3);
        yield DateTimeField::new('startTime', '开始有效时间')->setColumns(6)->setFormat('yyyy-MM-dd HH:mm:ss');
        yield DateTimeField::new('endTime', '结束有效时间')->setColumns(6)->setFormat('yyyy-MM-dd HH:mm:ss');
        yield TextareaField::new('allowedUsersDisplay', '允许的用户ID列表')
            ->setColumns(6)
            ->setHelp('JSON格式，如：["user1", "user2"]，留空表示不限制用户')
        ;
        yield TextareaField::new('allowedUserTagsDisplay', '允许的用户标签')
            ->setColumns(6)
            ->setHelp('JSON格式，如：["vip", "premium"]，留空表示不限制')
        ;
        yield BooleanField::new('isEnabled', '是否启用')->setColumns(3)->renderAsSwitch(false);
        yield DateTimeField::new('createTime', '创建时间')->setColumns(3)->setFormat('yyyy-MM-dd HH:mm:ss');
        yield DateTimeField::new('updateTime', '更新时间')->setColumns(3)->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isEnabled', '是否启用'))
            ->add(NumericFilter::new('maxUsagePerUser', '每人限领次数'))
            ->add(NumericFilter::new('maxTotalUsage', '总限领次数'))
            ->add(NumericFilter::new('currentUsage', '当前使用次数'))
            ->add(DateTimeFilter::new('startTime', '开始时间'))
            ->add(DateTimeFilter::new('endTime', '结束时间'))
        ;
    }
}
