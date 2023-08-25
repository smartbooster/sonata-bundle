<?php

namespace Smart\SonataBundle\DataFixtures\Processor;

use Fidry\AliceDataFixtures\ProcessorInterface;
use Smart\SonataBundle\Security\SmartUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class UserProcessor implements ProcessorInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * @inheritdoc
     * @param mixed $object
     */
    public function preProcess(string $fixtureId, $object): void
    {
        if (!$object instanceof SmartUserInterface) {
            return;
        }

        $object->setPassword($this->hasher->hashPassword($object, $object->getPlainPassword() ?? 'test'));
    }

    /**
     * @inheritdoc
     * @param mixed $object
     */
    public function postProcess(string $fixtureId, $object): void
    {
    }
}
