<?php

namespace Smart\SonataBundle;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * We only create this file to have access to the SF console and use it on make commands
 * All config in /config/packages are only meant to be minimal config for the bundle Kernel
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
