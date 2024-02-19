<?php

namespace Smart\SonataBundle;

use Smart\SonataBundle\DependencyInjection\Compiler\AdminCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class SmartSonataBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AdminCompilerPass());
    }
}
