<?php

namespace Smart\SonataBundle\Controller\Admin;

use Smart\CoreBundle\Utils\MarkdownUtils;
use Smart\SonataBundle\Mailer\BaseMailer;
use Smart\SonataBundle\Mailer\EmailProvider;
use Smart\SonataBundle\Mailer\TemplatedEmail;
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
    public const DEFAULT_PROCESS_TWIG = true;
    public const DEFAULT_MARKDOWN_TEMPLATE = '@SmartSonata/admin/documentation/markdown.html.twig';

    protected string $projectDir;
    protected Environment $twig;
    protected bool $processTwig = self::DEFAULT_PROCESS_TWIG;
    protected string $markdownTemplate = self::DEFAULT_MARKDOWN_TEMPLATE;

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

        return $this->renderEmailView($provider->getGroupedEmails());
    }

    /**
     * @param array<string, array<string, TemplatedEmail>> $groupedSmartEmails
     */
    protected function renderEmailView(array $groupedSmartEmails): Response
    {
        return new Response($this->twig->render('@SmartSonata/admin/documentation/email.html.twig', [
            'grouped_smart_emails' => $groupedSmartEmails,
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
        $directoryFinder = new Finder();
        foreach ($directoryFinder->directories()->in($this->projectDir . '/documentation')->sortByName(true) as $directory) {
            $directoryName = $directory->getFilename();
            $separator = strpos($directoryName, '-');
            if ($separator !== false) {
                $directoryPath = substr($directoryName, $separator + 1);
            } else {
                $directoryPath = $directoryName;
            }

            $content = $this->processDirectoryFiles(
                $directoryName,
                $directoryPath,
                $directoryParam,
                $filenameParam,
                $request,
                $markdownNav
            );

            if ($content !== null) {
                $markdownContent = $content;
            }
        }

        return $this->renderDocumentationView($markdownContent, $markdownNav);
    }

    /**
     * @param array<string, array<string, string>> $markdownNav
     */
    private function processDirectoryFiles(
        string $directoryName,
        string $directoryPath,
        string $directoryParam,
        string $filenameParam,
        Request $request,
        array &$markdownNav
    ): ?string {
        $markdownContent = null;
        $routeNamePrefix = RouteLoader::SMART_DOCUMENTATION_ROUTE_PREFIX;

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
                $rawContent = $file->getContents();

                if ($this->processTwig) {
                    $rawContent = $this->twig->createTemplate($rawContent)->render();
                }

                $markdownContent = $this->transformMarkdown(
                    $rawContent,
                    $request->getSchemeAndHttpHost() . $request->getRequestUri()
                );
            }
        }

        return $markdownContent;
    }

    /**
     * @param array<string, array<string, string>> $markdownNav
     */
    protected function renderDocumentationView(?string $markdownContent, array $markdownNav): Response
    {
        return new Response($this->twig->render($this->markdownTemplate, [
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

    public function setProcessTwig(bool $processTwig): void
    {
        $this->processTwig = $processTwig;
    }

    public function setMarkdownTemplate(string $markdownTemplate): void
    {
        $this->markdownTemplate = $markdownTemplate;
    }

    private function transformMarkdown(string $content, string $baseUrl): string
    {
        return MarkdownUtils::addAnchorToHeadings($content, $baseUrl);
    }
}
