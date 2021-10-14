<?php

namespace Smart\SonataBundle\Admin;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
abstract class AbstractAdmin extends \Sonata\AdminBundle\Admin\AbstractAdmin
{
    const ACTION_CREATE = 'CREATE';
    const ACTION_VIEW   = 'VIEW';
    const ACTION_EDIT   = 'EDIT';
    const ACTION_DELETE = 'DELETE';

    protected string $translationDomain = 'admin';

    protected ?Security $security = null;

    /**
     * Remove default batch as customer never really want default behavior
     */
    protected function configureBatchActions(array $actions): array
    {
        // We just unset the batch action 'delete' instead of returning null to keep extensions
        unset($actions['delete']);

        return $actions;
    }

    /**
     * Remove default export as customer never really want default behavior
     */
    public function getExportFormats(): array
    {
        return [];
    }

    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /**
     * @return UserInterface|null
     */
    protected function getUser(): ?UserInterface
    {
        if ($this->security === null) {
            throw new \Exception("Security service not inject. Add a call setSecurity on your admin service definition to access the User");
        }

        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }

    /**
     * @return bool
     */
    protected function isNew()
    {
        return $this->getSubject() == null || null === $this->getSubject()->getId();
    }
}
