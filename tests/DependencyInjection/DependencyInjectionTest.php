<?php

namespace Smart\SonataBundle\Tests\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sentry\ClientInterface;
use Smart\SonataBundle\SmartSonataBundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * vendor/bin/simple-phpunit tests/DependencyInjection/DependencyInjectionTest.php
 */
class DependencyInjectionTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $bundle = new SmartSonataBundle();
        $this->container = new ContainerBuilder();
        $this->container->setDefinition('Doctrine\ORM\EntityManagerInterface', new Definition(EntityManagerInterface::class));
        $this->container->setDefinition('Sentry\ClientInterface', new Definition(ClientInterface::class));
        $this->container->registerExtension($bundle->getContainerExtension());
        $bundle->build($this->container);
    }

    /**
     * @dataProvider invalidConfigurationProvider
     */
    public function testInvalidConfigurationParsing(string $resource, string $message): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($message);

        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__ . '/../fixtures/config/'));
        $loader->load($resource . ".yml");
        $this->container->compile();
    }

    public static function invalidConfigurationProvider(): array
    {
        return [
            'invalid_no_parameter_defined' => [
                'invalid_no_parameter_defined',
                'The path "smart_sonata.parameters" should have at least 1 element(s) defined.',
            ],
            'invalid_missing_parameter_value' => [
                'invalid_missing_parameter_value',
                'The child config "value" under "smart_sonata.parameters.parameter_without_value" must be configured.',
            ],
        ];
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testValidConfigurationParsing(string $resource): void
    {
        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__ . '/../fixtures/config/'));
        $loader->load($resource . ".yml");
        $this->container->compile();
        // This assertion is already true with the setUp but it valid the fact that the container did compile with the given configuration
        $this->assertNotNull($this->container->getExtension("smart_sonata")); // @phpstan-ignore-line
    }

    public static function configurationProvider(): array
    {
        return [
            'full' => ['full'],
            'minimal' => ['minimal'],
            'none' => ['none'],
        ];
    }
}
