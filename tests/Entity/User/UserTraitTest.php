<?php

namespace Smart\SonataBundle\Tests\Entity\User;

use PHPUnit\Framework\TestCase;

/**
 * vendor/bin/simple-phpunit tests/Entity/User/UserTraitTest.php
 *
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class UserTraitTest extends TestCase
{
    public function testFullname(): void
    {
        $user = new User();
        $user->setLastName('Doe')
            ->setFirstName('John');

        $this->assertEquals('John Doe', $user->getFullName());
    }
}
