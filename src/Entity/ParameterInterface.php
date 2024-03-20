<?php

namespace Smart\SonataBundle\Entity;

use Smart\SonataBundle\Entity\Log\HistorizableInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
interface ParameterInterface extends HistorizableInterface
{
    public function getId(): ?int;
    public function getCode(): string;
    public function setCode(string $code): void;
    public function getValue(): string|bool|float|int|null;
    public function getArrayValue(): array;
    public function isArrayValue(): bool;
    public function setValue(string|bool|null $value): void;
    public function getHelp(): ?string;
    public function setHelp(?string $help): void;
    public function getType(): string;
    public function isTextType(): bool;
    public function isEmailType(): bool;
    public function isEmailChainType(): bool;
    public function isBooleanType(): bool;
    public function isListType(): bool;
    public function isTextareaType(): bool;
    public function isIntegerType(): bool;
    public function isFloatType(): bool;
    public function setType(string $type): void;
    public function getRegex(): ?string;
    public function setRegex(?string $regex): void;
}
