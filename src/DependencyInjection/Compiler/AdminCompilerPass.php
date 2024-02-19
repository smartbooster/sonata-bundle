<?php

namespace Smart\SonataBundle\DependencyInjection\Compiler;

use Smart\SonataBundle\Admin\SmartAdminInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * MDT The old injection via services.yaml Smart\SonataBundle\Admin\AbstractAdmin abstract: true has been moved here because the latest version of
 * Sonata prevent to add method call via parent service, so that why we use the compiler pass instead.
 */
class AdminCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('sonata.admin')) as $adminId) {
            $adminDefinition = $container->getDefinition($adminId);
            $adminClass = $adminDefinition->getClass();
            if (!class_exists($adminClass) || !isset(class_implements($adminClass)[SmartAdminInterface::class])) {
                continue;
            }
            $adminDefinition->addMethodCall('setTokenManager', [new Reference('security.token_storage')]);
            $adminDefinition->addMethodCall('setContainer', [new Reference('Symfony\Component\DependencyInjection\ContainerInterface')]);
        }
    }
}
