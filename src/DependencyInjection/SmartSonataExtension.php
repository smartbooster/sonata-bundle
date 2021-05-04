<?php

namespace Smart\SonataBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class SmartSonataExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<object> $configs
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('admin_extension.xml');
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/config.yml'));

        foreach ($config as $name => $extension) {
            $container->prependExtensionConfig($name, $extension);
        }
    }
}
