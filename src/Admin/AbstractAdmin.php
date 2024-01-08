<?php

namespace Smart\SonataBundle\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Validator\Constraints\InlineConstraint;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
abstract class AbstractAdmin extends \Sonata\AdminBundle\Admin\AbstractAdmin
{
    public const ACTION_CREATE = 'CREATE';
    public const ACTION_VIEW   = 'VIEW';
    public const ACTION_EDIT   = 'EDIT';
    public const ACTION_DELETE = 'DELETE';

    /** @var ContainerInterface $container */
    private $container;
    private TokenStorageInterface $tokenStorage;

    public function __construct(string $code, ?string $class, string $baseControllerName = null)
    {
        parent::__construct();
        $this->init($code, $class, $baseControllerName);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export');
    }

    /**
     * Remove default batch as customer never really want default behavior
     */
    protected function configureBatchActions($actions): array
    {
        unset($actions['delete']);

        return $actions;
    }

    /**
     * @return UserInterface|null
     */
    protected function getUser()
    {
        $token = $this->tokenStorage->getToken();
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
        return $this->container->get($id);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setTokenManager(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return bool
     */
    protected function isNew()
    {
        return !$this->getSubject() || null === $this->getSubject()->getId(); // @phpstan-ignore-line
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $domain = $domain ?: $this->getTranslationDomain();

        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param mixed $attributes
     */
    public function smartIsGranted($attributes, ?object $object = null): bool
    {
        return true;
    }

    /**
     * Since sonata 4 validate method is gone. Custom implementation for retrieve his behavior
     * https://github.com/sonata-project/SonataAdminBundle/issues/6232
     */
    protected function configureFormOptions(array &$formOptions): void
    {
        parent::configureFormOptions($formOptions);
        $admin = $this;
        $formOptions['constraints'] = [
            new InlineConstraint([
                'service' => $this,
                'method' => static function (ErrorElement $errorElement, object $object) use ($admin) {
                    /* @var AdminInterface $admin */

                    // This avoid the main validation to be cascaded to children
                    // The problem occurs when a model Page has a collection of Page as property
                    if ($admin->hasSubject() && spl_object_hash($object) !== spl_object_hash($admin->getSubject())) {
                        return;
                    }

                    $admin->validate($errorElement, $object);
                },
                'serializingWarning' => true,
            ])
        ];
    }

    /**
     * Override for validate object
     */
    protected function validate(ErrorElement $errorElement, object $object): void
    {
        // do nothing
    }

    /**
     * @param string $code
     * @param class-string|null $class
     * @param string|null $baseControllerName
     * @return void
     */
    protected function init(string $code, ?string $class, string $baseControllerName = null): void
    {
        $this->setCode($code);
        $this->setModelClass($class);
        $this->setBaseControllerName($baseControllerName);
    }
}
