<?php

namespace Smart\SonataBundle\Logger;

use Doctrine\ORM\EntityManagerInterface;
use Smart\SonataBundle\Entity\Log\BatchLog;

class BatchLogger
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function log(array $parameters): void
    {
        $batchLog = new BatchLog();
        if (!isset($parameters['context'])) {
            throw new \Exception("context must be present in batch log");
        }
        $batchLog->setContext($parameters['context']);

        if (isset($parameters['name'])) {
            $batchLog->setName($parameters['name']);
        }

        if (isset($parameters['summary'])) {
            $batchLog->setSummary($parameters['summary']);
        }

        if (isset($parameters['raw_data'])) {
            $batchLog->setRawData($parameters['raw_data']);
        }

        if (isset($parameters['success'])) {
            $batchLog->setSuccess($parameters['success']);
        }

        $this->entityManager->persist($batchLog);
        $this->entityManager->flush();
    }
}
