<?php

namespace Smart\SonataBundle\Admin\Extension;

use Smart\CoreBundle\Entity\Log\HistorizableInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class HistoryExtension extends AbstractAdminExtension
{
    public function configureShowFields(ShowMapper $show): void
    {
        $subject = $show->getAdmin()->getSubject();
        if (!$subject instanceof HistorizableInterface) {
            return;
        }

        $tabs = $show->getAdmin()->getShowTabs();
        if (count($tabs) === 1 && $tabs[array_key_first($tabs)]['auto_created']) {
            $show->end();
        }
        $groups = $show->getAdmin()->getShowGroups();
        if (!isset($tabs['history_tab'])) {
            $show->tab('history_tab', ['label' => 'label.history']);
            if (!isset($groups['history_group'])) {
                $show->with('history_group', ['label' => 'label.history']);
            }
        }
        if (!$show->has('history')) {
            $show->add('history', null, ['template' => '@SmartSonata/admin/base_field/show_history_field.html.twig']);
        }
    }
}
