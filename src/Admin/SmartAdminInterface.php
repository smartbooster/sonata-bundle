<?php

namespace Smart\SonataBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
interface SmartAdminInterface
{
    /**
     * @deprecated Do a service injection via the __construct instead of using the get method
     */
    public function setContainer(ContainerInterface $container): void;

    /**
     * Require for the getUser method
     */
    public function setTokenManager(TokenStorageInterface $tokenStorage): void;

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string;

    /**
     * @param mixed $attributes
     */
    public function smartIsGranted($attributes, ?object $object = null): bool;
}
