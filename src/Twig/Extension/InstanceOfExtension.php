<?php

namespace Smart\SonataBundle\Twig\Extension;

use ReflectionClass;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceOfExtension extends AbstractExtension
{
    public function getTests()
    {
        return array(
            new TwigTest('instanceof', [$this, 'isInstanceOf']),
        );
    }

    /**
     * @param mixed $var
     * @param string $instance
     * @return bool
     * @throws \ReflectionException
     */
    public function isInstanceOf($var, $instance): bool
    {
        $reflexionClass = new ReflectionClass($instance);

        return $reflexionClass->isInstance($var);
    }
}
