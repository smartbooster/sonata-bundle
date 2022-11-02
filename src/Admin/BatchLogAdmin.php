<?php

namespace Smart\SonataBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;

/**
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 */
class BatchLogAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show']);
        parent::configureRoutes($collection);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('context', null, ['label' => 'field.label_context'])
            ->addIdentifier('name', null, ['label' => 'field.label_name'])
            ->add('createdAt', null, ['label' => 'field.label_date'])
            ->add('success', null, ['label' => 'field.label_success'])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('context', null, ['label' => 'field.label_context', 'show_filter' => true])
            ->add('name', null, ['label' => 'field.label_name', 'show_filter' => true])
            ->add('success', BooleanFilter::class, ['label' => 'field.label_success', 'show_filter' => true])
            ->add(
                'createdAt',
                DateTimeRangeFilter::class,
                [
                    'show_filter' => true,
                    'label' => 'field.label_date',
                    'field_type' => DateTimeRangePickerType::class,
                    'field_options' => [
                        'field_options_start' => ['format' => 'dd/MM/yyyy HH:mm'],
                        'field_options_end' => ['format' => 'dd/MM/yyyy HH:mm']
                    ]
                ]
            )
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('general', ['label' => 'fieldset.label_general', 'class' => 'col-md-4'])
                ->add('context', null, ['label' => 'field.label_context'])
                ->add('name', null, ['label' => 'field.label_name'])
                ->add('createdAt', null, ['label' => 'field.label_date'])
                ->add('success', null, ['label' => 'field.label_success'])
            ->end()
            ->with('log', ['label' => 'fieldset.label_log', 'class' => 'col-md-8'])
                ->add('summary', null, ['label' => 'field.label_summary'])
                ->add('rawData', null, ['label' => 'field.label_row_data'])
            ->end()
        ;
    }
}
