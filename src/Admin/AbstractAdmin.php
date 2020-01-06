<?php

namespace Smart\SonataBundle\Admin;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class AbstractAdmin extends \Sonata\AdminBundle\Admin\AbstractAdmin
{
    const ACTION_CREATE = 'CREATE';
    const ACTION_VIEW   = 'VIEW';
    const ACTION_EDIT   = 'EDIT';
    const ACTION_DELETE = 'DELETE';

    protected $translationDomain = 'admin';

    public function getLabelTranslatorStrategy()
    {
        return $this->get('sonata.admin.label.strategy.underscore');
    }

    /**
     * Remove default batch as customer never really want default behavior
     * @return array<string>
     */
    public function getBatchActions()
    {
        // On unset juste la batch action 'delete' plutôt que return null pour conserver les extensions
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    /**
     * Remove default export as customer never really want default behavior
     * @return array<string>|null
     */
    public function getExportFormats()
    {
        return null;
    }

    /**
     * Renove default mosaic as customer never really want default behavior
     * @return array<string, array<string, string>>
     */
    public function getListModes()
    {
        return  [
            'list' => [
                'class' => 'fa fa-list fa-fw',
            ]
        ];
    }

    /**
     * @return UserInterface|null
     */
    protected function getUser()
    {
        $token = $this->get('security.token_storage')->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function get($id)
    {
        return $this->getConfigurationPool()->getContainer()->get($id);
    }

    /**
     * @return bool
     */
    protected function isNew()
    {
        return !$this->getSubject() || null === $this->getSubject()->getId();
    }
}
