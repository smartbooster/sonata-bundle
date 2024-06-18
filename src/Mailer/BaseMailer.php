<?php

namespace Smart\SonataBundle\Mailer;

use Smart\CoreBundle\Entity\Log\HistorizableInterface;
use Smart\CoreBundle\Entity\MailableInterface;
use Smart\CoreBundle\Logger\HistoryLogger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseMailer
{
    private string $senderAddress;
    private string $senderName = '';
    protected ?string $recipientToString = null;
    protected MailerInterface $mailer;
    protected EmailProvider $provider;
    protected TranslatorInterface $translator;
    protected HistoryLogger $historyLogger;

    public function __construct(
        MailerInterface $mailer,
        EmailProvider $provider,
        TranslatorInterface $translator,
        HistoryLogger $historyLogger
    ) {
        $this->mailer = $mailer;
        $this->provider = $provider;
        $this->translator = $translator;
        $this->historyLogger = $historyLogger;
    }

    /**
     * @param array<string, string> $sender
     */
    public function setSender(array $sender): void
    {
        $this->senderAddress = $sender['address'];
        $this->senderName = $sender['name'] ?? '';
    }

    /**
     * If the wanted code email is in the EmailProvider configuration then it return the new instantiated TemplatedEmail
     *
     * We separate instancing from the send method to call some other TemplatedEmail methods if we need to
     * For example adding subjectParameters, cc, headers ...
     *
     * @param array<mixed> $context
     */
    public function newEmail(string $code, ?array $context = null): TemplatedEmail
    {
        $email = $this->provider->getEmail($code);
        if ($email === null) {
            throw new NotFoundHttpException("Email with code '$code' not found.");
        }
        $email->context($context);

        return $email;
    }

    /**
     * @param mixed $recipient
     */
    public function send(TemplatedEmail $email, $recipient = null): void
    {
        $email->subject($this->translator->trans($email->getSubject(), $email->getSubjectParameters(), 'email'));

        if ($email->getFrom() == null) {
            $email->from(new Address($this->senderAddress, $this->senderName));
        }
        $this->setRecipientToEmail($email, $recipient);

        $this->mailer->send($email);

        if ($recipient instanceof HistorizableInterface) {
            $historyData = [
                HistoryLogger::TITLE_PROPERTY => $email->getSubject(),
                HistoryLogger::RECIPIENT_PROPERTY => $this->recipientToString,
            ];

            $this->historyLogger->setFlushLog(true);
            $this->historyLogger->log($recipient, HistoryLogger::EMAIL_SENT_CODE, $historyData);
        }
    }

    /**
     * Protected method to allow ease extend and put custom logic based on the recipient
     * @param mixed $recipient
     */
    protected function setRecipientToEmail(TemplatedEmail $email, $recipient = null): void
    {
        if (
            ($recipient instanceof MailableInterface && $recipient->getRecipientEmail() === null)
            || (is_array($recipient) && empty($recipient))
            || $recipient == null
        ) {
            throw new \InvalidArgumentException($this->translator->trans('smart.email.empty_recipient_error', [
                '%code%' => $email->getCode()
            ], 'email'));
        }

        if ($recipient instanceof MailableInterface) {
            $this->recipientToString = $recipient->getRecipientEmail();
            $email->addTo(...$this->extractEmailFromString($recipient->getRecipientEmail()));
            $email->addCc(...$this->extractEmailFromString($recipient->getCc()));
            $email->addBcc(...$this->extractEmailFromString($recipient->getCci()));
        } elseif (is_array($recipient)) {
            $this->recipientToString = implode(', ', $recipient);
            $email->addTo(...$recipient);
        } else {
            $this->recipientToString = $recipient;
            $email->to(...$this->extractEmailFromString($recipient));
        }
    }

    private function extractEmailFromString(?string $string): array
    {
        if (null == $string) {
            return [];
        }

        if (str_contains($string, ',')) {
            $separator = ',';
        } elseif (str_contains($string, ';')) {
            $separator = ';';
        }
        if (!empty($separator)) {
            $toReturn = array_map(function ($elem) {
                return trim($elem);
            }, explode($separator, $string));
        } else {
            $toReturn = [$string];
        }

        return $toReturn;
    }
}
