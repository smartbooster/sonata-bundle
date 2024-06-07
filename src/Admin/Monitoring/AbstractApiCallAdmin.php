<?php

declare(strict_types=1);

namespace Smart\SonataBundle\Admin\Monitoring;

use Smart\CoreBundle\Entity\ApiCallInterface;
use Smart\CoreBundle\Enum\ProcessStatusEnum;
use Smart\CoreBundle\Monitoring\ApiCallMonitor;
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

abstract class AbstractApiCallAdmin extends AbstractAdmin
{
    private ApiCallMonitor $apiCallMonitor;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
        $collection->add('restartApiCall', $this->getRouterIdParameter() . '/restart-api-call');
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'startedAt';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('origin', ChoiceFilter::class, [
                'label' => 'label.origin',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getOriginChoices(),
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'label.result',
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
            ->add('type', ChoiceFilter::class, [
                'label' => 'label.route',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->getRouteChoices(),
                ],
            ])
            ->add('routeUrl', null, [
                'label' => 'label.route_url',
                'show_filter' => true,
            ])
            ->add('statusCode', null, [
                'label' => 'label.status_code',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'field.label_id'])
            ->add('startedAt', null, ['label' => 'label.started_at'])
            ->add('origin', FieldDescriptionInterface::TYPE_CHOICE, [
                'label' => 'label.origin',
                'choices' => array_flip($this->getOriginChoices()),
            ])
            ->add('statusCode', null, [
                'label' => 'label.result',
                'template' => '@SmartSonata/admin/base_field/list_api_call_status_code.html.twig',
            ])
            ->add('method', null, ['label' => 'label.method'])
            ->add('routeUrl', null, ['label' => 'label.route_url'])
            ->add('durationAsString', null, ['label' => 'label.duration'])
            ->add('summary', null, [
                'label' => 'label.summary',
                'template' => '@SmartSonata/admin/base_field/list_nl2br.html.twig',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        /** @var ApiCallInterface $subject */
        $subject = $this->getSubject();
        $show
            ->with('general', ['label' => 'fieldset.label_general', 'class' => 'col-md-4'])
                ->add('id', null, ['label' => 'field.label_id'])
                ->add('type', FieldDescriptionInterface::TYPE_CHOICE, [
                    'label' => 'label.route',
                    'choices' => array_flip($this->getRouteChoices()),
                ])
                ->add('startedAt', null, ['label' => 'label.started_at'])
                ->add('endedAt', null, ['label' => 'label.ended_at'])
                ->add('durationAsString', null, ['label' => 'label.duration'])
                ->add('statusAsString', FieldDescriptionInterface::TYPE_CHOICE, [
                    'label' => 'label.status',
                    'choices' => array_flip($this->getStatusChoices()),
                ])
        ;
        if ($subject->getRestartedAt()) {
            $show->add('restartedAt', null, ['label' => 'label.restarted_at']);
        }
        $show
                ->add('summary', null, ['label' => 'label.summary'])
            ->end()
            ->with('api_params', ['label' => 'label.api_params', 'class' => 'col-md-8'])
                ->add('origin', FieldDescriptionInterface::TYPE_CHOICE, [
                    'label' => 'label.origin',
                    'choices' => array_flip($this->getOriginChoices()),
                ])
                ->add('statusCode', null, [
                    'label' => 'label.result',
                    'template' => '@SmartSonata/admin/base_field/show_api_call_status_code.html.twig',
                ])
                ->add('method', null, ['label' => 'label.method'])
                ->add('routeUrl', null, ['label' => 'label.route_url'])
                ->add('inputData', null, [
                    'label' => 'label.input_data',
                    'template' => '@SmartSonata/admin/base_field/show_json.html.twig',
                ])
                ->add('headers', null, [
                    'label' => 'label.headers',
                    'template' => '@SmartSonata/admin/base_field/show_json.html.twig',
                ])
                ->add(
                    'outputResponse',
                    is_array($subject->getOutputResponse()) ? FieldDescriptionInterface::TYPE_ARRAY : FieldDescriptionInterface::TYPE_STRING,
                    [
                        'label' => 'label.output_response',
                        'template' => '@SmartSonata/admin/base_field/show_json.html.twig',
                    ],
                )
            ->end()
            ->with('process_data', ['label' => 'label.process_data', 'class' => 'pull-right col-md-8'])
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

    public function setApiCallMonitor(ApiCallMonitor $apiCallMonitor): void
    {
        $this->apiCallMonitor = $apiCallMonitor;
    }

    public function getApiRestartAllowedRoutes(): array
    {
        return $this->apiCallMonitor->getRestartAllowedRoutes();
    }

    abstract protected function getRouteChoices(): array;

    abstract protected function getOriginChoices(): array;
}
