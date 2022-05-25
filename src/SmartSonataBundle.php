<?php

namespace Smart\SonataBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class SmartSonataBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
