<?php

namespace Smart\SonataBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Smart\SonataBundle\Entity\Parameter;
use Smart\SonataBundle\Entity\ParameterInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ParameterProvider
{
    private EntityManagerInterface $entityManager;
    private array $parameters;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->parameters = [];
    }

    /**
     * Get a Parameter instance
     *
     * @throws EntityNotFoundException
     */
    public function get(string $code): ParameterInterface
    {
        if (isset($this->parameters[$code])) {
            return $this->parameters[$code];
        }

        $parameter = $this->entityManager->getRepository(Parameter::class)->findOneBy(['code' => $code]);

        if ($parameter === null) {
            throw new EntityNotFoundException("The parameter with code \"$code\" was not found.");
        }
        $this->parameters[$code] = $parameter;

        return $parameter;
    }

    /**
     * Get a Parameter value
     */
    public function getValue(string $code): array|bool|float|int|string
    {
        $parameter = $this->get($code);
        if ($parameter->isArrayValue()) {
            return $parameter->getArrayValue();
        } else {
            return $parameter->getValue();
        }
    }
}
