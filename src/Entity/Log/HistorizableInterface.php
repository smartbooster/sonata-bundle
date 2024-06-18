<?php

namespace Smart\SonataBundle\Entity\Log;

/**
 * @deprecated use Smart\CoreBundle\Entity\Log\HistorizableInterface instead
 *
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
interface HistorizableInterface
{
    public function getHistory(): ?array;

    public function setHistory(?array $history): self;

    public function addHistory(array $history): self;

    public function getDataForHistoryDiff(): array;

    public function getAttributsForHistoryDiff(): array;
}
