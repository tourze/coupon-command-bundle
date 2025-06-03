<?php

namespace Tourze\CouponCommandBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\CouponCommandBundle\Entity\CommandLimit;
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;

class CommandLimitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandLimit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('口令限制配置')
            ->setEntityLabelInPlural('口令限制配置')
            ->setPageTitle('index', '口令限制配置管理')
            ->setPageTitle('new', '创建限制配置')
            ->setPageTitle('edit', '编辑限制配置')
            ->setPageTitle('detail', '限制配置详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('新建限制');
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

        $fields[] = AssociationField::new('commandConfig', '关联口令')
            ->setColumns(6)
            ->setRequired(true);

        $fields[] = IntegerField::new('maxUsagePerUser', '每人限领次数')
            ->setColumns(3)
            ->setHelp('留空表示不限制');

        $fields[] = IntegerField::new('maxTotalUsage', '总限领次数')
            ->setColumns(3)
            ->setHelp('留空表示不限制');

        $fields[] = IntegerField::new('currentUsage', '当前已使用次数')
            ->setColumns(3)
            ->hideOnForm();

        $fields[] = DateTimeField::new('startTime', '开始有效时间')
            ->setColumns(6)
            ->setHelp('留空表示立即生效')
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        $fields[] = DateTimeField::new('endTime', '结束有效时间')
            ->setColumns(6)
            ->setHelp('留空表示永久有效')
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        $fields[] = TextareaField::new('allowedUsers', '允许的用户ID列表')
            ->setColumns(6)
            ->setHelp('JSON格式，如：["user1", "user2"]，留空表示不限制用户')
            ->formatValue(function ($value) {
                return $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '';
            })
            ->onlyOnDetail();

        $fields[] = TextareaField::new('allowedUserTags', '允许的用户标签')
            ->setColumns(6)
            ->setHelp('JSON格式，如：["vip", "premium"]，留空表示不限制')
            ->formatValue(function ($value) {
                return $value ? json_encode($value, JSON_UNESCAPED_UNICODE) : '';
            })
            ->onlyOnDetail();

        $fields[] = BooleanField::new('isEnabled', '是否启用')
            ->setColumns(3)
            ->renderAsSwitch(true);

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
            ->add(BooleanFilter::new('isEnabled', '是否启用'))
            ->add(NumericFilter::new('maxUsagePerUser', '每人限领次数'))
            ->add(NumericFilter::new('maxTotalUsage', '总限领次数'))
            ->add(NumericFilter::new('currentUsage', '当前使用次数'));
    }
} 