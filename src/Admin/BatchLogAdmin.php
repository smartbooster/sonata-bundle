<?php

namespace Smart\SonataBundle\Admin;

use Smart\SonataBundle\Entity\Log\BatchLog;
use Smart\SonataBundle\Repository\Log\BatchLogRepository;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 */
class BatchLogAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show', 'delete']);
        parent::configureRoutes($collection);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'date';
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('context', null, [
                'label' => 'field.label_context',
                'template' => '@SmartSonata/admin/base_field/list_translated_field.html.twig'
            ])
            ->addIdentifier('name', null, [
                'label' => 'field.label_name',
                'template' => '@SmartSonata/admin/base_field/list_translated_field.html.twig'
            ])
            ->add('date', null, ['label' => 'field.label_date'])
            ->add('success', null, ['label' => 'field.label_success'])
            ->add('summary', null, [
                'label' => 'field.label_summary',
                'template' => '@SmartSonata/admin/base_field/list_html_field.html.twig'
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getModelManager();
        /** @var BatchLogRepository $batchLogRepository */
        $batchLogRepository = $modelManager
            ->getEntityManager(BatchLog::class)
            ->getRepository(BatchLog::class);

        $filter
            ->add('context', ChoiceFilter::class, [
                'label' => 'field.label_context',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getChoicesValues($batchLogRepository->getAllContexts()),
                    'multiple' => true
                ],
            ])
            ->add('name', ChoiceFilter::class, [
                'label' => 'field.label_name',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getChoicesValues($batchLogRepository->getAllNames()),
                    'multiple' => true
                ],
            ])
            ->add('success', BooleanFilter::class, ['label' => 'field.label_success', 'show_filter' => true])
            ->add(
                'date',
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
                ->add('context', null, [
                    'label' => 'field.label_context',
                    'template' => '@SmartSonata/admin/base_field/show_translated_field.html.twig'
                ])
                ->add('name', null, [
                    'label' => 'field.label_name',
                    'template' => '@SmartSonata/admin/base_field/show_translated_field.html.twig'
                ])
                ->add('date', null, ['label' => 'field.label_date'])
                ->add('success', null, ['label' => 'field.label_success'])
            ->end()
            ->with('log', ['label' => 'fieldset.label_log', 'class' => 'col-md-8'])
                ->add('summary', null, [
                    'label' => 'field.label_summary',
                    'template' => '@SmartSonata/admin/base_field/show_html_field.html.twig'
                ])
                ->add('comment', null, [
                    'label' => 'field.label_comment',
                    'template' => '@SmartSonata/admin/base_field/show_html_field.html.twig'
                ])
                ->add('data', null, [
                    'label' => 'field.label_data',
                    'template' => '@SmartSonata/admin/base_field/show_pre_field.html.twig'
                ])
            ->end()
        ;
    }

    private function getChoicesValues(array $values): array
    {
        $allTransValues = array_map(function (string $value) {
            return $this->trans($value);
        }, $values);

        return array_combine($allTransValues, $values);
    }
}
