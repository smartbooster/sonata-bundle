<?php

declare(strict_types=1);

namespace Smart\SonataBundle\Admin\Monitoring;

use Smart\CoreBundle\Command\CommandPoolHelper;
use Smart\CoreBundle\Enum\ProcessStatusEnum;
use Smart\SonataBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractCronAdmin extends AbstractAdmin
{
    private CommandPoolHelper $commandPoolHelper;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'startedAt';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('type', ChoiceFilter::class, [
                'label' => 'label.type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->commandPoolHelper->getCronChoices(),
                    'choice_translation_domain' => 'messages',
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'label.status',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getStatusChoices(),
                    'choice_translation_domain' => false,
                ],
            ])
            ->add('startedAt', DateTimeRangeFilter::class, [
                'label' => 'label.started_at',
                'show_filter' => true,
                'field_type' => DateTimeRangePickerType::class,
                'field_options' => [
                    'field_options_start' => ['format' => 'dd/MM/yyyy HH:mm'],
                    'field_options_end' => ['format' => 'dd/MM/yyyy HH:mm']
                ]
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'field.label_id'])
            ->addIdentifier('type', FieldDescriptionInterface::TYPE_CHOICE, [
                'label' => 'label.type',
                'choices' => array_flip($this->commandPoolHelper->getCronChoices()),
                'choice_translation_domain' => 'messages',
                'sortable' => false,
            ])
            ->add('startedAt', null, ['label' => 'label.started_at'])
            ->add('durationAsString', null, ['label' => 'label.duration'])
            ->add('status', null, [
                'label' => 'label.status',
                'template' => '@SmartSonata/admin/base_field/list_process_status.html.twig',
            ])
            ->add('summary', null, [
                'label' => 'label.summary',
                'sortable' => false,
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('label_general', ['label' => 'fieldset.label_general', 'class' => 'col-md-4'])
                ->add('id', null, ['label' => 'field.label_id'])
                ->add('type', FieldDescriptionInterface::TYPE_CHOICE, [
                    'label' => 'label.type',
                    'choices' => array_flip($this->commandPoolHelper->getCronChoices()),
                    'choice_translation_domain' => 'messages',
                ])
                ->add('startedAt', null, ['label' => 'label.started_at'])
                ->add('endedAt', null, ['label' => 'label.ended_at'])
                ->add('durationAsString', null, ['label' => 'label.duration'])
                ->add('status', null, [
                    'label' => 'label.status',
                    'template' => '@SmartSonata/admin/base_field/show_process_status.html.twig',
                ])
                ->add('summary', null, ['label' => 'label.summary'])
            ->end()
            ->with('process_data', ['label' => 'label.process_data', 'class' => 'col-md-8'])
                ->add('logs', null, [
                    'label' => 'label.logs',
                    'template' => '@SmartSonata/admin/base_field/show_process_logs.html.twig',
                ])
                ->add('data', null, [
                    'label' => 'label.process_json_data',
                    'template' => '@SmartSonata/admin/base_field/show_json.html.twig',
                ])
            ->end()
        ;
    }

    private function getStatusChoices(): array
    {
        return ProcessStatusEnum::casesByTrans($this->getTranslator(), true, 'messages');
    }

    #[Required]
    public function setCommandPoolHelper(CommandPoolHelper $commandPoolHelper): void
    {
        $this->commandPoolHelper = $commandPoolHelper;
    }
}
