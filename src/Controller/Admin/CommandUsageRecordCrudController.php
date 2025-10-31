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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\CouponCommandBundle\Entity\CommandUsageRecord;

/**
 * @extends AbstractCrudController<CommandUsageRecord>
 */
#[AdminCrud(
    routePath: '/coupon-command/usage-record',
    routeName: 'coupon_command_usage_record',
)]
final class CommandUsageRecordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CommandUsageRecord::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('口令使用记录')
            ->setEntityLabelInPlural('口令使用记录')
            ->setPageTitle('index', '口令使用记录')
            ->setPageTitle('detail', '使用记录详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['userId', 'commandText', 'couponId'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('查看详情');
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
        yield AssociationField::new('commandConfig', '口令配置')->setColumns(4);
        yield TextField::new('userId', '用户ID')->setColumns(3)->setMaxLength(255);
        yield TextField::new('commandText', '使用的口令')->setColumns(3)->setMaxLength(255);
        yield TextField::new('couponId', '获得的优惠券ID')->setColumns(3)->setMaxLength(255);
        yield BooleanField::new('isSuccess', '是否成功')->setColumns(2)->renderAsSwitch(false);
        yield TextField::new('createdBy', '创建人')->setColumns(3)->setMaxLength(255);
        yield DateTimeField::new('createTime', '使用时间')->setColumns(3)->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    /** @return iterable<FieldInterface> */
    private function getFormFields(): iterable
    {
        yield TextField::new('userId', '用户ID')->setColumns(3)->setMaxLength(255);
        yield TextField::new('commandText', '使用的口令')->setColumns(3)->setMaxLength(255);
        yield TextField::new('couponId', '获得的优惠券ID')->setColumns(3)->setMaxLength(255);
        yield BooleanField::new('isSuccess', '是否成功')->setColumns(2)->renderAsSwitch(true);
        yield TextField::new('failureReason', '失败原因')->setColumns(4)->setMaxLength(255);
    }

    /** @return iterable<FieldInterface> */
    private function getDetailFields(): iterable
    {
        // 显示所有字段，包括JSON字段
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield AssociationField::new('commandConfig', '口令配置')->setColumns(4);
        yield TextField::new('userId', '用户ID')->setColumns(3)->setMaxLength(255);
        yield TextField::new('commandText', '使用的口令')->setColumns(3)->setMaxLength(255);
        yield TextField::new('couponId', '获得的优惠券ID')->setColumns(3)->setMaxLength(255);
        yield BooleanField::new('isSuccess', '是否成功')->setColumns(2)->renderAsSwitch(false);
        yield TextField::new('failureReason', '失败原因')->setColumns(4)->setMaxLength(255);
        yield TextareaField::new('extraDataDisplay', '额外信息')
            ->setColumns(12)
        ;
        yield TextField::new('createdBy', '创建人')->setColumns(3)->setMaxLength(255);
        yield TextField::new('createdFromIp', '创建IP')->setColumns(3)->setMaxLength(255);
        yield DateTimeField::new('createTime', '使用时间')->setColumns(3)->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('commandConfig', '口令配置'))
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(TextFilter::new('commandText', '口令'))
            ->add(TextFilter::new('couponId', '优惠券ID'))
            ->add(BooleanFilter::new('isSuccess', '是否成功'))
            ->add(DateTimeFilter::new('createTime', '使用时间'))
        ;
    }
}
