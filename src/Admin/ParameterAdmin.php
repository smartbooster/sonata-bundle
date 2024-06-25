<?php

namespace Smart\SonataBundle\Admin;

use Smart\CoreBundle\Validator\Constraints\EmailChain;
use Smart\SonataBundle\Entity\ParameterInterface;
use Smart\SonataBundle\Enum\ParameterTypeEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Email;
use Yokai\EnumBundle\Form\Type\EnumType;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 * @method ParameterInterface getSubject()
 */
class ParameterAdmin extends AbstractAdmin
{
    private ParameterTypeEnum $typeEnum;
    public ?string $showHistoryTemplate = '@SmartSonata/admin/parameter_admin/show_history_field.html.twig';

    public function __construct(
        string $code,
        ?string $class,
        string $baseControllerName,
        ParameterTypeEnum $typeEnum,
    ) {
        parent::__construct($code, $class, $baseControllerName);
        $this->typeEnum = $typeEnum;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
        parent::configureRoutes($collection);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'field.label_id'])
            ->addIdentifier('code', null, ['label' => 'field.label_code'])
            ->add('type', FieldDescriptionInterface::TYPE_CHOICE, [
                'label' => 'field.label_type',
                'choices' => array_flip($this->typeEnum->getChoices()),
            ])
            ->add('help', null, ['label' => 'field.label_help'])
            ->add('value', null, [
                'label' => 'field.label_value',
                'template' => '@SmartSonata/admin/parameter_admin/list_value.html.twig'
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code', null, [
                'show_filter' => true,
                'label' => 'field.label_code'
            ])
            ->add('type', ChoiceFilter::class, [
                'field_type' => EnumType::class,
                'field_options' => [
                    'enum' => ParameterTypeEnum::class,
                    'choice_translation_domain' => false,
                ],
                'show_filter' => true,
                'label' => 'field.label_type',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $parameter = $this->getSubject();
        $show
            ->with('fieldset.label_general', ['label' => 'fieldset.label_general'])
                ->add('id', null, ['label' => 'field.label_id'])
                ->add('code', null, ['label' => 'field.label_code'])
                ->add('type', FieldDescriptionInterface::TYPE_CHOICE, [
                    'label' => 'field.label_type',
                    'choices' => array_flip($this->typeEnum->getChoices()),
                    'catalog' => false, // enum is self translated
                ])
        ;
        if ($parameter->getHelp()) {
            $show->add('help', null, ['label' => 'field.label_help']);
        }
        if ($parameter->getRegex() !== null) {
            $show->add('regex', null, ['label' => 'field.label_regex']);
        }
        $valueType = null;
        if ($parameter->isBooleanType()) {
            $valueType = FieldDescriptionInterface::TYPE_BOOLEAN;
        } elseif ($parameter->isTextareaType()) {
            $valueType = FieldDescriptionInterface::TYPE_HTML;
        }
        $show
                ->add('value', $valueType, ['label' => 'field.label_value'])
            ->end()
            ->with('fieldset.label_history', ['class' => 'col-md-12', 'label' => 'fieldset.label_history'])
                ->add('historyLegacy', null, ['template' => '@SmartSonata/admin/parameter_admin/timeline_history_field.html.twig'])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $parameter = $this->getSubject();
        $form
            ->with('fieldset.label_general', ['label' => 'fieldset.label_general'])
                ->add('code', null, [
                    'label' => 'field.label_code',
                    'disabled' => true
                ])
                ->add('type', EnumType::class, [
                    'label' => 'field.label_type',
                    'enum'  => ParameterTypeEnum::class,
                    'disabled' => true,
                ])
        ;

        if ($parameter->getHelp()) {
            $form
                ->add('help', null, [
                    'label' => 'field.label_help',
                    'disabled' => true
                ])
            ;
        }
        if ($parameter->getRegex() !== null) {
            $form->add('regex', null, [
                'label' => 'field.label_regex',
                'disabled' => true,
            ]);
        }

        $valueType = TextType::class;
        $valueOptions = [];
        if ($parameter->isBooleanType()) {
            $valueType = CheckboxType::class;
            $valueOptions = ['required' => false];
        } elseif ($parameter->isEmailChainType() || $parameter->isListType() || $parameter->isTextareaType()) {
            $valueType = TextareaType::class;
            $valueOptions = ['attr' => ['rows' => 10]];
            if (!$parameter->isTextareaType()) {
                $valueOptions['help'] = 'field.help_parameter_array_values';
            }
            if ($parameter->isEmailChainType()) {
                $valueOptions['constraints'] = [new EmailChain(separator: PHP_EOL)];
            }
        } elseif ($parameter->isFloatType() || $parameter->isIntegerType()) {
            $valueType = NumberType::class;
            $valueOptions = ['html5' => true];
            if ($parameter->isIntegerType()) {
                $valueOptions['scale'] = 0;
            }
        } elseif ($parameter->isEmailType()) {
            // MDT Should be converted with Assert\When for when we drop support for SF 5.4
            $valueOptions['constraints'] = [new Email()];
        }
        $form->add('value', $valueType, [
            'label' => 'field.label_value',
            ...$valueOptions,
        ])->end();
    }

    public function getExportFormats(): array
    {
        return ['csv'];
    }

    protected function configureExportFields(): array
    {
        return [
            $this->trans('field.label_code') => 'code',
            $this->trans('field.label_value') => 'value',
            $this->trans('field.label_help') => 'help',
        ];
    }
}
