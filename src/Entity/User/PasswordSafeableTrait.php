<?php

namespace Smart\SonataBundle\Entity\User;

use Smart\CoreBundle\Validator\Constraints as SmartAssert;

trait PasswordSafeableTrait
{
    /**
     * @var ?string
     *
     * @SmartAssert\IsPasswordSafe
     */
    private ?string $plainPassword = null;
}
