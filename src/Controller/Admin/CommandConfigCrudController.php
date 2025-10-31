<?php

namespace Tourze\CouponCommandBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCommandBundle\Entity\CommandConfig;

/**
 * @extends AbstractCrudController<CommandConfig>
 */
#[AdminCrud(
    routePath: '/coupon-command/config',
    routeName: 'coupon_command_config',
)]
final class CommandConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('优惠券口令')
            ->setEntityLabelInPlural('优惠券口令')
            ->setPageTitle('index', '优惠券口令管理')
            ->setPageTitle('new', '创建优惠券口令')
            ->setPageTitle('edit', '编辑优惠券口令')
            ->setPageTitle('detail', '优惠券口令详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['command'])
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
            ->disable(Action::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('command', '口令')
            ->setColumns(6)
            ->setRequired(true)
            ->setHelp('请输入口令内容，不能重复')
            ->setMaxLength(255)
        ;

        // coupon字段的ORM映射被注释掉，暂时隐藏此字段
        // yield AssociationField::new('coupon', '关联优惠券')
        //     ->setColumns(6)
        //     ->setRequired(true)
        //     ->hideOnIndex()
        // ;

        yield AssociationField::new('commandLimit', '使用限制')
            ->setColumns(12)
            ->onlyOnDetail()
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('usageCount', '使用次数')
                ->setColumns(4)
                ->onlyOnDetail()
                ->formatValue(function (mixed $value, CommandConfig $entity): int {
                    return $entity->getUsageRecords()->count();
                })
            ;
        }

        yield TextField::new('createdBy', '创建人')
            ->setColumns(3)
            ->hideOnForm()
        ;

        yield TextField::new('updatedBy', '更新人')
            ->setColumns(3)
            ->hideOnForm()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setColumns(3)
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setColumns(3)
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('command', '口令'))
            ->add(EntityFilter::new('coupon', '关联优惠券'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
