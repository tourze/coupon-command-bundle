<?php

namespace Tourze\CouponCommandBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCommandBundle\Entity\CommandConfig;
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;

class CommandConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandConfig::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('优惠券口令')
            ->setEntityLabelInPlural('优惠券口令')
            ->setPageTitle('index', '优惠券口令管理')
            ->setPageTitle('new', '创建优惠券口令')
            ->setPageTitle('edit', '编辑优惠券口令')
            ->setPageTitle('detail', '优惠券口令详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['command'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('新建口令');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [];

        $fields[] = IdField::new('id', 'ID')
            ->onlyOnDetail();

        $fields[] = TextField::new('command', '口令')
            ->setColumns(6)
            ->setRequired(true)
            ->setHelp('请输入口令内容，不能重复');

        $fields[] = AssociationField::new('coupon', '关联优惠券')
            ->setColumns(6)
            ->setRequired(true)
            ->hideOnIndex();

        $fields[] = AssociationField::new('commandLimit', '使用限制')
            ->setColumns(12)
            ->onlyOnDetail()
            ->setTemplatePath('@EasyAdmin/crud/field/association.html.twig');

        // 统计信息字段（只在详情页显示）
        if (Crud::PAGE_DETAIL === $pageName) {
            $fields[] = TextField::new('usageCount', '使用次数')
                ->setColumns(4)
                ->onlyOnDetail()
                ->formatValue(function ($value, $entity) {
                    return $entity->getUsageRecords()->count();
                });
        }

        $fields[] = TextField::new('createdBy', '创建人')
            ->setColumns(3)
            ->hideOnForm();

        $fields[] = TextField::new('updatedBy', '更新人')
            ->setColumns(3)
            ->hideOnForm();

        $fields[] = DateTimeField::new('createTime', '创建时间')
            ->setColumns(3)
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        $fields[] = DateTimeField::new('updateTime', '更新时间')
            ->setColumns(3)
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        return $fields;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('command', '口令'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'));
    }
}
