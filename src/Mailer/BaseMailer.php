<?php

namespace Smart\SonataBundle\Mailer;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseMailer
{
    protected MailerInterface $mailer;
    protected EmailProvider $provider;
    protected TranslatorInterface $translator;
    private string $mailFrom;

    public function __construct(MailerInterface $mailer, EmailProvider $provider, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->provider = $provider;
        $this->translator = $translator;
        $this->mailFrom = (string) $parameterBag->get('smart_sonata.mail_from');
    }

    /**
     * If the wanted code email is in the EmailProvider configuration then it return the new instantiated TemplatedEmail
     *
     * We separate instancing from the send method to call some other TemplatedEmail methods if we need to
     * For example adding subjectParameters, cc, headers ...
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
