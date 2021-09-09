<?php

namespace Smart\SonataBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ImportDenormalizeException extends \UnexpectedValueException
{
    public array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;

        parent::__construct(
            'smart_sonata.import.denormalize_error',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            null
        );
    }
}
