<?php

namespace Smart\SonataBundle\Tests\Entity\User;

use Smart\SonataBundle\Entity\User\UserTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Dummy class to test UserTrait
 *
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class User implements UserInterface, \Serializable
{
    use UserTrait;
}
