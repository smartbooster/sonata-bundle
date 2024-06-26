<?php

namespace Smart\SonataBundle\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Add methods for resetting password
 *
 * todo in v3 extends the MailableInterface and UserProfileInterface
 */
interface SmartUserInterface extends PasswordAuthenticatedUserInterface, UserInterface
{
    /**
     * Set the password used to authenticate the user.
     *
     * @param  string $password encoded password
     *
     * @return void
     */
    public function setPassword($password);

    /**
     * This should not be stored, used only in resetting password process
     *
     * @return ?string
     */
    public function getPlainPassword();

    /**
     * Set plain password (this should not be stored)
     *
     * @param ?string $plainPassword
     *
     * @return void
     */
    public function setPlainPassword($plainPassword);

    /**
     * Ensure the user has a getEmail method
     *
     * @return string
     */
    public function getEmail();
}
