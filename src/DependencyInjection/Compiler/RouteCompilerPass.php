<?php

namespace Smart\SonataBundle\DependencyInjection\Compiler;

use Smart\SonataBundle\Route\RouteLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * MDT CompilerPass needed to inject the kernel.project_dir paramter required by the RouteLoader to parse documentation files structure.
 */
class RouteCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition(RouteLoader::class)->setArguments([$container->getParameter('kernel.project_dir')]);
    }
}
