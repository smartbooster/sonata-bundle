<?php

namespace Smart\SonataBundle\Entity\Log;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
interface HistorizableInterface
{
    public function getHistory(): ?array;

    public function setHistory(?array $history): self;

    public function addHistory(array $history): self;
}
