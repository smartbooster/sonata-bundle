<?php

namespace Smart\SonataBundle\DependencyInjection;

use Smart\SonataBundle\Mailer\BaseMailer;
use Smart\SonataBundle\Mailer\EmailProvider;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        // todo check to use logical paths instead https://symfony.com/doc/5.4/bundles/best_practices.html#resources
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        $emailProvider = $container->getDefinition(EmailProvider::class);
        $emailProvider->addMethodCall('setTranslateEmail', [$config['translate_email']]);
        if (isset($config['emails']) && is_array($config['emails'])) {
            $emailProvider->addMethodCall('setEmailCodes', [$config['emails']]);
        }
        $baseMailer = $container->getDefinition(BaseMailer::class);
        $baseMailer->addMethodCall('setSender', [$config['sender']]);
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = Yaml::parse((string) file_get_contents(__DIR__ . '/../../config/bundle_prepend_config.yml'));

        foreach ($config as $name => $extension) {
            $container->prependExtensionConfig($name, $extension);
        }
    }
}
