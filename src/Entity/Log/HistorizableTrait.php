<?php

namespace Smart\SonataBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
trait HistorizableTrait
{
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
}
