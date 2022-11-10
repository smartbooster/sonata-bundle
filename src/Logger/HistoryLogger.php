<?php

namespace Smart\SonataBundle\Logger;

use Doctrine\ORM\EntityManagerInterface;
use Smart\SonataBundle\Entity\Log\HistorizableInterface;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class HistoryLogger
{
    const EMAIL_SENT_CODE = 'email.sent';
    const ENTITY_CREATED_CODE = 'entity.created';
    const ENTITY_UPDATED_CODE = 'entity.updated';
    const ENTITY_ARCHIVED_CODE = 'entity.archived';
    const ERROR_CODE = 'error';
    const EXTERNAL_CODE = 'external';
    const INTERNE_CODE = 'interne';

    const ADMIN_CONTEXT = 'admin';
    const API_CONTEXT = 'api';
    const FRONT_CONTEXT = 'front';
    const CRON_CONTEXT = 'cron';

    protected EntityManagerInterface $entityManager;
    protected array $beforeHandleEntityData = [];
    protected array $afterHandleEntityData = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Set entity data before processing for comparison during update history log
     */
    public function init(array $entityData): void
    {
        $this->beforeHandleEntityData = $entityData;
    }

    /**
     * Call this method with the data after update processing and use the return bool to condition the log call
     */
    public function hasDiff(array $entityData): bool
    {
        $this->afterHandleEntityData = $entityData;

        return $this->beforeHandleEntityData !== $this->afterHandleEntityData;
    }

    /**
     * @param array $data Optionnal extra data to add more information
     */
    public function log(HistorizableInterface $entity, string $code, array $data = []): void
    {
        $history = array_merge([
            'code' => $code,
            'date' => time(),
        ], $data);

        if ($history['code'] === self::ENTITY_UPDATED_CODE && !empty($this->afterHandleEntityData)) {
            $history['history_updated_diff'] = [
                'before' => $this->beforeHandleEntityData,
                'after' => $this->afterHandleEntityData,
            ];
        }

        $entity->addHistory($history);

        $this->entityManager->flush();
    }

    /**
     * check and add $updatedEntity diff log between $initalEntity and $updatedEntity
     */
    public function logEntityDiff(HistorizableInterface $initalEntity, HistorizableInterface $updatedEntity, string $code, array $data = []): void
    {
        $this->logDataDiff(
            $updatedEntity,
            $initalEntity->getDataForHistoryDiff(),
            $updatedEntity->getDataForHistoryDiff(),
            $code,
            $data
        );
    }

    /**
     * check and add $entity diff log between $initialData and $updatedData
     */
    public function logDataDiff(HistorizableInterface $entity, array $initialData, array $updatedData, string $code, array $data = []): void
    {
        $this->init(array_intersect_key($initialData, array_flip($entity->getAttributsForHistoryDiff())));
        if ($this->hasDiff($updatedData)) {
            $this->log($entity, $code, $data);
        }
    }
}
