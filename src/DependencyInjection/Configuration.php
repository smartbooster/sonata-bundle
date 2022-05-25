<?php

namespace Smart\SonataBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('smart_sonata');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->variableNode('emails')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
