<?php

namespace Smart\SonataBundle\Controller\Admin;

use Smart\SonataBundle\Mailer\BaseMailer;
use Smart\SonataBundle\Mailer\EmailProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentationController extends AbstractController
{
    public function email(
        Request $request,
        EmailProvider $provider,
        ValidatorInterface $validator,
        BaseMailer $mailer,
        TranslatorInterface $translator
    ): Response {
        $emails = $provider->getEmails();

        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var array<string, string> $data */
            $data = $request->request->all();
            $recipient = $data['email_recipient'];
            if ($validator->validate($recipient, new Email())->count() > 0) {
                $this->addFlash('danger', $translator->trans('smart.email.test_form.email_error', [
                    '%recipient%' => $recipient,
                ], 'email'));
            } else {
                $code = $data['email_code'];
                $untranslatedSubject = $emails[$code]->getSubject(); // we store the subject trans key before sending it

                $mailer->send($emails[$code], $recipient);
                $this->addFlash('success', $translator->trans('smart.email.test_form.success', [
                    '%code%' => $code,
                    '%recipient%' => $recipient,
                ], 'email'));

                $emails[$code]->subject($untranslatedSubject); // that way we set it back the trans key for the doc
            }
        }

        return $this->render("@SmartSonata/admin/documentation/email.html.twig", [
            'grouped_smart_emails' => $provider->getGroupedEmails(),
        ]);
    }
}
