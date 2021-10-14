<?php

namespace Smart\SonataBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;

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
    public function __construct($action)
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

        if (!$list->has('_action')) {
            $this->createActionField($list);

            return;
        }

        $field = $list->get('_action');

        if ('actions' === $field->getType()) {
            $this->alterActionField($field);
        }
    }

    /**
     * @param FieldDescriptionInterface $field
     * @return void
     */
    private function alterActionField(FieldDescriptionInterface $field)
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
        $list->add('_action', 'actions', [
            'actions' => [
                $this->action => [
                    'template' => sprintf('@SmartSonata/action/%s.html.twig', $this->action)
                ]
            ]
        ]);
    }
}
