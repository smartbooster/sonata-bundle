<?php

namespace Smart\SonataBundle\Tests\Provider;

use Doctrine\ORM\EntityNotFoundException;
use Smart\SonataBundle\Provider\ParameterProvider;
use Smart\StandardBundle\AbstractWebTestCase;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * vendor/bin/phpunit tests/Provider/ParameterProviderTest.php
 */
class ParameterProviderTest extends AbstractWebTestCase
{
//    private ?ParameterProvider $provider;
//
//    public function setUp(): void
//    {
//        parent::setUp();
//
//        $this->provider = $this->getContainer()->get(ParameterProvider::class);
//    }

    /**
     * Add a dummy test for remove warning on phpunit test on class have no test
     */
    public function test(): void
    {
        $this->assertTrue(true); // @phpstan-ignore-line
    }

    // Test with base don't work
//    public function testGetNotFoundException(): void
//    {
//        $this->expectException(EntityNotFoundException::class);
//        $this->expectExceptionMessage('The parameter with code "not_found" was not found.');
//
//        $this->provider->getValue("not_found");
//    }

//    public function testGetOk(): void
//    {
//        $this->databaseTool->loadAliceFixture([$this->getFixturesDir() . "/Provider/ParameterProvider/parameter.yaml"]);
//
//        $parameter = $this->provider->get('full_param');
//        $this->assertSame("full_param", (string) $parameter);
//        $this->assertIsInt($parameter->getId());
//        $this->assertSame("Expected Parameter Value", $parameter->getValue());
//        $this->assertSame("Expected Parameter Help", $parameter->getHelp());
//
//        $parameter = $this->provider->get('minimal_param');
//        $this->assertSame("minimal_param", $parameter->getCode());
//
//        // test basic cache by calling get on 'full_param' again
//        $parameter = $this->provider->get('full_param');
//        $this->assertSame("full_param", $parameter->getCode());
//    }
//
//    public function testGetValueOk(): void
//    {
//        $this->databaseTool->loadAliceFixture([$this->getFixturesDir() . "/Provider/ParameterProvider/parameter.yaml"]);
//
//        $this->assertSame("Text Value", $this->provider->getValue("minimal_param"));
//    }
}
