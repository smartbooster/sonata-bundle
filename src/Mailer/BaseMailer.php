<?php

namespace Smart\SonataBundle\Mailer;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseMailer
{
    protected MailerInterface $mailer;
    protected TranslatorInterface $translator;
    private string $mailFrom;

    public function __construct(MailerInterface $mailer, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->mailFrom = (string) $parameterBag->get('smart_sonata.mail_from');
    }

    /**
     * @param mixed $recipient
     */
    public function send(TemplatedEmail $email, $recipient = null): void
    {
        $email->subject($this->translator->trans($email->getSubject(), $email->getSubjectParameters(), 'email'));

        if ($email->getFrom() == null) {
            $email->from($this->mailFrom);
        }
        $this->setRecipientToEmail($email, $recipient);

        $this->mailer->send($email);
    }

    /**
     * Protected method to allow ease extend and put custom logic based on the recipient
     * @param mixed $recipient
     */
    protected function setRecipientToEmail(TemplatedEmail $email, $recipient = null): void
    {
        if ($recipient == null) {
            throw new \InvalidArgumentException($this->translator->trans('smart.email.empty_recipient_error', [
                '%code%' => $email->getCode()
            ], 'email'));
        }

        $email->to($recipient);
    }
}
