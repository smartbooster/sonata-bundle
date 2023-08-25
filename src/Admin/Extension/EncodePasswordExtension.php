<?php

namespace Smart\SonataBundle\Admin\Extension;

use Smart\SonataBundle\Security\SmartUserInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class EncodePasswordExtension extends AbstractAdminExtension
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * {@inheritDoc}
     * @param SmartUserInterface $user
     */
    public function preUpdate(AdminInterface $admin, $user): void
    {
        $this->hashPassword($user);
    }

    /**
     * {@inheritDoc}
     * @param SmartUserInterface $user
     */
    public function prePersist(AdminInterface $admin, $user): void
    {
        $this->hashPassword($user);
    }

    /**
     * @param SmartUserInterface $user
     */
    private function hashPassword(SmartUserInterface $user): void
    {
        if ("" === trim($user->getPlainPassword())) {
            return;
        }

        $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
    }
}
