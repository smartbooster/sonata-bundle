<?php

namespace Smart\SonataBundle\Admin;

use Sonata\AdminBundle\Translator\UnderscoreLabelTranslatorStrategy;
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
        // Remove default mosaic as customer never really want default behavior
        $this->setListModes([
            'list' => [
                'class' => 'fa fa-list fa-fw',
            ]
        ]);
        $this->setLabelTranslatorStrategy(new UnderscoreLabelTranslatorStrategy());
        $this->setTranslationDomain('admin');
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
     * Remove default export as customer never really want default behavior
     * @return string[]
     */
    public function getExportFormats(): array
    {
        return [];
    }

    /**
     * @return UserInterface|null
     */
    protected function getUser()
    {
        $token = $this->get('security.token_storage')->getToken(); // @phpstan-ignore-line
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
}
