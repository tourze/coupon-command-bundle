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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;
use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;

class CommandUsageRecordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandUsageRecord::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('口令使用记录')
            ->setEntityLabelInPlural('口令使用记录')
            ->setPageTitle('index', '口令使用记录')
            ->setPageTitle('detail', '使用记录详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['userId', 'commandText', 'couponId'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('查看详情');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [];

        $fields[] = IdField::new('id', 'ID')
            ->onlyOnDetail();

        $fields[] = AssociationField::new('commandConfig', '口令配置')
            ->setColumns(4)
            ->hideOnForm();

        $fields[] = TextField::new('userId', '用户ID')
            ->setColumns(3);

        $fields[] = TextField::new('commandText', '使用的口令')
            ->setColumns(3);

        $fields[] = TextField::new('couponId', '获得的优惠券ID')
            ->setColumns(3);

        $fields[] = BooleanField::new('isSuccess', '是否成功')
            ->setColumns(2)
            ->renderAsSwitch(false);

        $fields[] = TextField::new('failureReason', '失败原因')
            ->setColumns(4)
            ->hideOnIndex()
            ->setMaxLength(255);

        $fields[] = TextareaField::new('extraData', '额外信息')
            ->setColumns(12)
            ->onlyOnDetail()
            ->formatValue(function ($value) {
                return $value ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
            });

        $fields[] = TextField::new('createdBy', '创建人')
            ->setColumns(3)
            ->hideOnForm();

        $fields[] = TextField::new('createdFromIp', '创建IP')
            ->setColumns(3)
            ->hideOnForm()
            ->hideOnIndex();

        $fields[] = DateTimeField::new('createTime', '使用时间')
            ->setColumns(3)
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        return $fields;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(TextFilter::new('commandText', '口令'))
            ->add(TextFilter::new('couponId', '优惠券ID'))
            ->add(BooleanFilter::new('isSuccess', '是否成功'))
            ->add(DateTimeFilter::new('createTime', '使用时间'));
    }
}
