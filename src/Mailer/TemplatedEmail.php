<?php

namespace Smart\SonataBundle\Mailer;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class TemplatedEmail extends \Symfony\Bridge\Twig\Mime\TemplatedEmail
{
    /** @var string Unique email identifier */
    private string $code;

    /** @var mixed[] Used for subject parameters translation in AbstractMailer */
    private array $subjectParameters = [];

    public function __construct(string $code, ?string $locale = null)
    {
        parent::__construct();

        $this->code = $code;
        $this->subject("$code.subject");
        $this->htmlTemplate(sprintf(
            "email/%s%s.html.twig",
            $locale != null ? "$locale/" : '',
            str_replace('.', '/', $code)
        ));
    }

    public function code(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param mixed[] $subjectParameters
     */
    public function subjectParameters(array $subjectParameters): self
    {
        $this->subjectParameters = $subjectParameters;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getSubjectParameters(): array
    {
        return $this->subjectParameters;
    }

    public function getDocumentationTitle(): string
    {
        return $this->getCode() . ".doc_title";
    }
}
