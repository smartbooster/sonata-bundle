<?php

namespace Smart\SonataBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
trait HistorizableTrait
{
    private ?PropertyAccessorInterface $propertyAccess = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    protected ?array $history = null;

    public function getHistory(): ?array
    {
        return $this->history;
    }

    public function setHistory(?array $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function addHistory(array $history): self
    {
        if ($this->history == null) {
            $this->history = [];
        }
        array_unshift($this->history, $history);

        return $this;
    }

    public function getDataForHistoryDiff(): array
    {
        $propertyAccessor = $this->getPropertyAccessor();

        $toReturn = [];
        foreach ($this->getAttributsForHistoryDiff() as $attribut) {
            $toReturn[$attribut] = $propertyAccessor->getValue($this, $attribut);
        }

        return $toReturn;
    }

    /**
     * Redefine in entity
     */
    public function getAttributsForHistoryDiff(): array
    {
        return [];
    }

    private function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (!$this->propertyAccess instanceof PropertyAccessorInterface) {
            $this->propertyAccess = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        }

        return $this->propertyAccess;
    }
}
