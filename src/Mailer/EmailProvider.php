<?php

namespace Smart\SonataBundle\Mailer;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class EmailProvider
{
    private string $locale;
    /** @var ?array<string> */
    private ?array $emailCodes = null;
    private bool $translateEmail = false;
    /** @var ?array<TemplatedEmail> */
    private ?array $emails = null;

    public function __construct(RequestStack $requestStack)
    {
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
    }

    public function setTranslateEmail(bool $translateEmail): void
    {
        $this->translateEmail = $translateEmail;
    }

    /**
     * @param array<string> $emailCodes
     */
    public function setEmailCodes(array $emailCodes): void
    {
        $this->emailCodes = $emailCodes;
    }

    /**
     * @return array<string>
     */
    private function getEmailCodes(): array
    {
        return $this->emailCodes;
    }

    /**
     * @return array<TemplatedEmail>
     */
    public function getEmails(): array
    {
        if ($this->emails !== null) {
            return $this->emails;
        }

        $toReturn = [];

        foreach ($this->getEmailCodes() as $code) {
            $toReturn[$code] = new TemplatedEmail($code, $this->translateEmail ? $this->locale : null);
        }

        $this->emails = $toReturn;

        return $toReturn;
    }

    public function getEmail(string $code): ?TemplatedEmail
    {
        if (isset($this->getEmails()[$code])) {
            return $this->getEmails()[$code];
        }

        return null;
    }

    /**
     * What we call a group for email is the first dotted string on an email code
     * For example, the group for the email code 'admin.security.forgot_password' will be 'admin'
     *
     * @return array<string, array<string, TemplatedEmail>>
     */
    public function getGroupedEmails(): array
    {
        $groups = [];
        foreach ($this->getEmailCodes() as $code) {
            $groups[] = substr($code, 0, (int) strpos($code, '.'));
        }
        $groups = array_unique($groups);

        $toReturn = [];
        foreach ($groups as $group) {
            $toReturn[$group] = $this->filterEmailsByGroup($group);
        }

        return $toReturn;
    }

    /**
     * @return array<string, TemplatedEmail>
     */
    private function filterEmailsByGroup(string $group): array
    {
        $toReturn = $this->getEmails();

        //@phpstan-ignore-next-line
        return array_filter($toReturn, function ($email) use ($group) {
            return preg_match("/^$group\./", $email->getCode());
        });
    }
}
