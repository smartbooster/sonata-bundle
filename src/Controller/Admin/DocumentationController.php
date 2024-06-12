<?php

namespace Smart\SonataBundle\Controller\Admin;

use Smart\CoreBundle\Utils\MarkdownUtils;
use Smart\SonataBundle\Mailer\BaseMailer;
use Smart\SonataBundle\Mailer\EmailProvider;
use Smart\SonataBundle\Route\RouteLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function Symfony\Component\String\u;

class DocumentationController extends AbstractController
{
    private string $projectDir;
    private Environment $twig;

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

        return new Response($this->twig->render("@SmartSonata/admin/documentation/email.html.twig", [
            'grouped_smart_emails' => $provider->getGroupedEmails(),
        ]));
    }

    public function renderMarkdown(Request $request): Response
    {
        $routePath = $request->getPathInfo();
        $directoryFilename = explode(DIRECTORY_SEPARATOR, str_replace('/documentation/', '', $routePath));
        $directoryParam = $directoryFilename[0];
        $filenameParam = $directoryFilename[1];

        $markdownContent = null;
        $markdownNav = [];
        $routeNamePrefix = RouteLoader::SMART_DOCUMENTATION_ROUTE_PREFIX;
        $directoryFinder = new Finder();
        foreach ($directoryFinder->directories()->in($this->projectDir . '/documentation')->sortByName(true) as $directory) {
            $directoryName = $directory->getFilename();
            $separator = strpos($directoryName, '-');
            if ($separator !== false) {
                $directoryPath = substr($directoryName, $separator + 1);
            } else {
                $directoryPath = $directoryName;
            }

            $mdFinder = new Finder();
            $mdFinder->files()->in($this->projectDir . '/documentation/' . $directoryName)->name('*.md')->sortByName(true);
            foreach ($mdFinder as $file) {
                $filename = $file->getFilename();
                $separator = strpos($filename, '-');
                if ($separator !== false) {
                    $filename = substr($filename, $separator + 1);
                }
                $filename = u($filename)->replace('.md', '')->snake()->toString();
                $snakeDirectoryName = u($directoryPath)->snake()->toString();
                $markdownNav[$snakeDirectoryName][$filename] = $routeNamePrefix . $snakeDirectoryName . '_' . $filename;

                if (str_ends_with($directoryName, $directoryParam) && $filename === $filenameParam) {
                    $markdownContent = $this->transformMarkdown(
                        $file->getContents(),
                        $request->getSchemeAndHttpHost() . $request->getRequestUri()
                    );
                }
            }
        }

        return new Response($this->twig->render('@SmartSonata/admin/documentation/markdown.html.twig', [
            'markdown_content' => $markdownContent,
            'markdown_nav' => $markdownNav,
        ]));
    }

    public function setProjectDir(string $projectDir): void
    {
        $this->projectDir = $projectDir;
    }

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    private function transformMarkdown(string $content, string $baseUrl): string
    {
        return MarkdownUtils::addAnchorToHeadings($content, $baseUrl);
    }
}
