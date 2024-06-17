<?php

namespace Smart\SonataBundle\Twig\Extension;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class HistoryExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('history_get_row_icon_name', [$this, 'getRowIconName']),
            new TwigFunction('history_get_row_title', [$this, 'getRowTitle']),
        ];
    }

    public function getRowIconName(array $row): ?string
    {
        if (isset($row['success'])) {
            return 'check';
        }

        return match ($row['code']) {
            'email.sent' => 'envelope',
            'crt' => 'plus',
            'upd' => 'update',
            'arc' => 'archive',
            'stripe' => 'stripe',
            'api' => 'abbr-api',
            'import' => 'import',
            'cron' => 'cog',
            default => null,
        };
    }

    public function getRowTitle(array $row, string $domain = 'admin'): ?string
    {
        $codeTitle = $this->translator->trans('history.' . $row['code'], [], $domain);
        $toReturn = $row['title'] ?? null;

        switch ($row['code']) {
            case 'email.sent':
            case 'import':
            case 'cron':
                if (!empty($row['prefixCodeTitle'])) {
                    $toReturn = $codeTitle . ($toReturn !== null ? (' : ' . $toReturn) : '');
                }
                break;
            case 'crt':
            case 'upd':
            case 'arc':
                $toReturn = $codeTitle;
                break;
        }

        return $toReturn;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
