<?php

namespace Smart\SonataBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class ImportExtension extends AbstractAdminExtension
{
    private string $action = 'import';

    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add($this->action, $this->action, [
            # https://symfony.com/index.php/bundles/SonataAdminBundle/3.x/cookbook/recipe_decouple_crud_controller.html
            '_controller' => 'Smart\SonataBundle\Controller\ImportController::import',
        ]);
    }

    public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
    {
        $list = parent::configureActionButtons($admin, $list, $action, $object);

        if (!$admin->isGranted(strtoupper($this->action))) {
            return $list;
        }

        if (!isset($list[$this->action])) {
            $list[$this->action]['template'] = sprintf('@SmartSonata/action/%s.html.twig', $this->action);
        }

        return $list;
    }
}
