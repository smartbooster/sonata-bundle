<?php

namespace Smart\SonataBundle\Tests;

use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Kernel used by PHPUnit to ease services testing
 *
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \DAMA\DoctrineTestBundle\DAMADoctrineTestBundle(),
            new \Liip\TestFixturesBundle\LiipTestFixturesBundle(),
            new \Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle(),
            new \Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle(),
            new \Smart\SonataBundle\SmartSonataBundle(),
            new \Sonata\AdminBundle\SonataAdminBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Yokai\SecurityTokenBundle\YokaiSecurityTokenBundle(),
            new \Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new \Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new \Sentry\SentryBundle\SentryBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config_test.yml');
    }

    // https://github.com/dmaicher/doctrine-test-bundle/blob/master/tests/Functional/app/AppKernel.php
    protected function build(ContainerBuilder $container): void
    {
        // remove logger info
        $container->register('logger', NullLogger::class);
    }
}
