<?php

namespace Smart\SonataBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Entity\Log\HistorizableTrait;
use Smart\CoreBundle\Utils\ArrayUtils;
use Smart\SonataBundle\Enum\ParameterTypeEnum;
use Sonata\Exporter\Exception\InvalidMethodCallException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * @ORM\Table(name="smart_parameter")
 * @ORM\Entity(repositoryClass="Smart\SonataBundle\Repository\ParameterRepository")
 */
#[ORM\Table(name: "smart_parameter")]
#[ORM\Entity(repositoryClass: "Smart\SonataBundle\Repository\ParameterRepository")]
class Parameter implements ParameterInterface
{
    use HistorizableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @ORM\Column(name="code", unique=true, nullable=false)
     */
    #[ORM\Column(name: "code", unique: true, nullable: false)]
    private string $code;

    /**
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    #[ORM\Column(name: "value", type: Types::TEXT, nullable: false)]
    private string|bool|null $value = null;

    /**
     * @ORM\Column(name="help", type="text", nullable=true)
     */
    #[ORM\Column(name: "help", type: Types::TEXT, nullable: true)]
    private ?string $help = null;

    /**
     * @ORM\Column(length=15, nullable=false, options={"default"="text"})
     */
    #[ORM\Column(length: 15, nullable: false, options: ['default' => 'text'])]
    private string $type = ParameterTypeEnum::TEXT;

    /**
     * MDT We don't need to set the start/end delimiter when setting the regex, it is automatically added when the validate callback is triggered
     * @ORM\Column(length=100, nullable=true)
     * @Assert\Length(max=100)
     */
    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $regex = null;

    /**
     * @ORM\Column(nullable=true)
     */
    #[ORM\Column(nullable: true)]
    protected ?array $historyLegacy = null;

    /**
     * @param mixed $payload
     * @Assert\Callback
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        // MDT Should be converted with Assert\When for when we drop support for SF 5.4
        if (!$this->isBooleanType() && $this->getValue() === null || $this->getValue() === '') {
            $context->buildViolation("This value should not be blank.")
                ->atPath('value')
                ->addViolation();
        }

        $regex = $this->getRegex();
        if ($regex !== null && $this->isTextType() && !preg_match('/' . $regex . '/', (string) $this->getValue())) {
            $context->buildViolation("smart_parameter.regex_error")
                ->atPath('value')
                ->addViolation();
        } elseif ($regex !== null && $this->isListType()) {
            foreach ($this->getArrayValue() as $rowValue) {
                if (!preg_match('/' . $regex . '/', (string) $rowValue)) {
                    $unvalidRowValues[] = $rowValue;
                }
            }
            if (!empty($unvalidRowValues)) {
                $context->buildViolation("smart_parameter.list_regex_error", [
                    '%errors%' => implode(', ', $unvalidRowValues)
                ])
                    ->atPath('value')
                    ->addViolation();
            }
        }
    }

    public function __toString()
    {
        return $this->getCode();
    }

    public function isArrayValue(): bool
    {
        return $this->isListType() || $this->isEmailChainType();
    }

    public function isTextType(): bool
    {
        return $this->getType() === ParameterTypeEnum::TEXT;
    }

    public function isEmailType(): bool
    {
        return $this->getType() === ParameterTypeEnum::EMAIL;
    }

    public function isEmailChainType(): bool
    {
        return $this->getType() === ParameterTypeEnum::EMAIL_CHAIN;
    }

    public function isBooleanType(): bool
    {
        return $this->getType() === ParameterTypeEnum::BOOLEAN;
    }

    public function isListType(): bool
    {
        return $this->getType() === ParameterTypeEnum::LIST;
    }

    public function isTextareaType(): bool
    {
        return $this->getType() === ParameterTypeEnum::TEXTAREA;
    }

    public function isIntegerType(): bool
    {
        return $this->getType() === ParameterTypeEnum::INTEGER;
    }

    public function isFloatType(): bool
    {
        return $this->getType() === ParameterTypeEnum::FLOAT;
    }

    public function getAttributsForHistoryDiff(): array
    {
        return ['value'];
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
     * @return string|bool|float|int|null
     */
    public function getValue(): string|bool|float|int|null
    {
        $value = $this->value;
        if ($this->isBooleanType()) {
            $toReturn = (bool) $value;
        } elseif ($this->isFloatType()) {
            $toReturn = (float) $value;
        } elseif ($this->isIntegerType()) {
            $toReturn = (int) $value;
        } else {
            $toReturn = $this->value;
        }

        return $toReturn;
    }

    public function getArrayValue(): array
    {
        if (!$this->isArrayValue()) {
            throw new InvalidMethodCallException("You can't use this method for a parameter that isn't a list or an email chain.");
        }

        return ArrayUtils::getArrayFromTextarea((string) $this->getValue());
    }

    /**
     * @param string|bool|null $value
     */
    public function setValue(string|bool|null $value): void
    {
        if ($this->isBooleanType() && $value === false) {
            $value = '0';
        }
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex(?string $regex): void
    {
        $this->regex = $regex;
    }

    public function getHistoryLegacy(): ?array
    {
        return $this->historyLegacy;
    }
}
