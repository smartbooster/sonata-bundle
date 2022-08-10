<?php

namespace Smart\SonataBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ActionExtension extends AbstractAdminExtension
{
    /**
     * @var string
     */
    private $action;

    /**
     * @param string $action
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * @param ListMapper $list
     * @return void
     */
    public function configureListFields(ListMapper $list): void
    {
        if (!$list->getAdmin()->isGranted(strtoupper($this->action))) {
            return;
        }

        if (!$list->has(ListMapper::NAME_ACTIONS)) {
            $this->createActionField($list);

            return;
        }

        $field = $list->get(ListMapper::NAME_ACTIONS);

        if ('actions' === $field->getType()) {
            $this->alterActionField($field);
        }
    }

    /**
     * @param FieldDescriptionInterface $field
     * @return void
     */
    private function alterActionField(FieldDescriptionInterface $field): void
    {
        $field->setOption(
            'actions',
            array_merge(
                $field->getOption('actions'),
                [
                    $this->action => [
                        'template' => sprintf('@SmartSonata/action/%s.html.twig', $this->action)
                    ]
                ]
            )
        );
    }

    /**
     * @param ListMapper $list
     * @return void
     */
    private function createActionField(ListMapper $list)
    {
        $list->add(ListMapper::NAME_ACTIONS, 'actions', [
            'actions' => [
                $this->action => [
                    'template' => sprintf('@SmartSonata/action/%s.html.twig', $this->action)
                ]
            ]
        ]);
    }
}
