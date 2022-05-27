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
     * What we call domain for email is the first dotted string on an email code
     * For example, the domain for the email code 'admin.security.forgot_password' will be 'admin'
     *
     * @return array<string, array<string, TemplatedEmail>>
     */
    public function getEmailsGroupByDomain(): array
    {
        $domains = [];
        foreach ($this->getEmailCodes() as $code) {
            $domains[] = substr($code, 0, (int) strpos($code, '.'));
        }
        $domains = array_unique($domains);

        $toReturn = [];
        foreach ($domains as $domain) {
            $toReturn[$domain] = $this->filterEmailsByDomain($domain);
        }

        return $toReturn;
    }

    /**
     * @return array<string, TemplatedEmail>
     */
    private function filterEmailsByDomain(string $domain): array
    {
        $toReturn = $this->getEmails();

        //@phpstan-ignore-next-line
        return array_filter($toReturn, function ($email) use ($domain) {
            return preg_match("/^$domain\./", $email->getCode());
        });
    }
}
