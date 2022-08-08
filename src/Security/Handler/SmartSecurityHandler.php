<?php

namespace Smart\SonataBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;

/**
 * Custom security handler to add custom logic before the original isGranted
 *
 * https://docs.sonata-project.org/projects/SonataAdminBundle/en/4.x/reference/advanced_configuration/#custom-action-access-management
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class SmartSecurityHandler implements SecurityHandlerInterface
{
    private SecurityHandlerInterface $defaultSecurityHandler;

    public function __construct(SecurityHandlerInterface $defaultSecurityHandler)
    {
        $this->defaultSecurityHandler = $defaultSecurityHandler;
    }

    public function isGranted(AdminInterface $admin, $attributes, ?object $object = null): bool
    {
        // @phpstan-ignore-next-line
        if (!$admin->smartIsGranted($attributes, $object)) {
            return false;
        }

        return $this->defaultSecurityHandler->isGranted($admin, $attributes, $object);
    }

    public function getBaseRole(AdminInterface $admin): string
    {
        return $this->defaultSecurityHandler->getBaseRole($admin);
    }

    public function buildSecurityInformation(AdminInterface $admin): array
    {
        return $this->defaultSecurityHandler->buildSecurityInformation($admin);
    }

    public function createObjectSecurity(AdminInterface $admin, object $object): void
    {
        $this->defaultSecurityHandler->createObjectSecurity($admin, $object);
    }

    public function deleteObjectSecurity(AdminInterface $admin, object $object): void
    {
        $this->defaultSecurityHandler->deleteObjectSecurity($admin, $object);
    }
}
