<?php

namespace Smart\SonataBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
#[ORM\Table(name: "smart_parameter")]
#[ORM\Entity(repositoryClass: "Smart\SonataBundle\Repository\ParameterRepository")]
class Parameter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "code", unique: true, nullable: false)]
    private string $code;

    #[ORM\Column(name: "value", type: Types::TEXT, nullable: false)]
    private string $value;

    #[ORM\Column(name: "help", type: Types::TEXT, nullable: true)]
    private ?string $help = null;

    public function __toString()
    {
        return $this->getCode();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }

    /**
     * @param string|null $help
     */
    public function setHelp(?string $help): void
    {
        $this->help = $help;
    }
}
