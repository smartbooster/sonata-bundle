<?php

namespace Smart\SonataBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class FormatExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_iso8601_datetime', [$this, 'isIso8601Datetime']),
        ];
    }

    public function isIso8601Datetime(?string $date): bool
    {
        try {
            if ($date == null) {
                return false;
            }
            $dt = new \DateTime($date);
            return $dt->format(\DateTime::ATOM) === $date;
        } catch (\Exception $e) {
            return false;
        }
    }
}
