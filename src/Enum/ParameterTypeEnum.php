<?php

namespace Smart\SonataBundle\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;
use Yokai\EnumBundle\TranslatedEnum;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ParameterTypeEnum extends TranslatedEnum
{
    public const TEXT = 'text';
    public const EMAIL = 'email';
    public const EMAIL_CHAIN = 'email_chain';
    public const BOOLEAN = 'boolean';
    public const LIST = 'list';
    public const TEXTAREA = 'textarea';
    public const INTEGER = 'integer';
    public const FLOAT = 'float';

    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct([
            self::TEXT,
            self::EMAIL,
            self::EMAIL_CHAIN,
            self::BOOLEAN,
            self::LIST,
            self::TEXTAREA,
            self::INTEGER,
            self::FLOAT,
        ], $translator, 'enum.parameter_type.%s');
    }
}
