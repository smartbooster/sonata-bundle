<?php

namespace Smart\SonataBundle\DependencyInjection;

use Smart\SonataBundle\Controller\Admin\DocumentationController;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('smart_sonata');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('sender')
                    ->isRequired()
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('address')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->booleanNode('translate_email')->defaultFalse()->end()
                ->arrayNode('emails')
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                ->end()
                ->append($this->getParametersNode())
                ->arrayNode('documentation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('process_twig')
                            ->defaultValue(DocumentationController::DEFAULT_PROCESS_TWIG)
                            ->info('Disables Twig processing in documentation Markdown files when set to false.')
                        ->end()
                        ->scalarNode('markdown_template')
                            ->defaultValue(DocumentationController::DEFAULT_MARKDOWN_TEMPLATE)
                            ->info('Template used to render Markdown documentation.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getParametersNode(): ArrayNodeDefinition
    {
        return (new TreeBuilder('parameters'))->getRootNode()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('type')->defaultValue('text')->end()
                    ->scalarNode('value')->isRequired()->end()
                    ->scalarNode('regex')->end()
                    ->scalarNode('help')->end()
                ->end()
            ->end()
            ;
    }
}
