<?php

namespace Smart\SonataBundle\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Validator\Constraints\InlineConstraint;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /** @var ContainerInterface $container */
    private $container;

    public function __construct(string $code, ?string $class, string $baseControllerName = null)
    {
        parent::__construct($code, $class, $baseControllerName);
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
        return $this->container->get($id);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
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
}
