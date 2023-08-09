<?php

namespace Smart\SonataBundle\Config;

/**
 * Service générique pour gérer les override des options php ini (dont la mémoire)
 */
class IniOverrideConfig
{
    private string $defaultMemoryLimit;
    private ?string $batchMemory;

    /**
     * @param ?string $batchMemory basé sur la variable d'env PLATFORM_BATCH_MEMORY
     */
    public function __construct(?string $batchMemory)
    {
        $this->batchMemory = $batchMemory;
        $this->defaultMemoryLimit = $this->getCurrentMemoryLimit();
    }

    private function getDefaultMemoryLimit(): string
    {
        return $this->defaultMemoryLimit;
    }

    public function getCurrentMemoryLimit(): string
    {
        return ini_get('memory_limit');
    }

    public function increaseMemoryLimit(): void
    {
        if ($this->batchMemory === null || (int) $this->batchMemory <= (int) $this->getCurrentMemoryLimit()) {
            return;
        }
        ini_set('memory_limit', $this->batchMemory);
    }

    public function resetMemoryLimit(): void
    {
        ini_set('memory_limit', $this->getDefaultMemoryLimit());
    }
}
