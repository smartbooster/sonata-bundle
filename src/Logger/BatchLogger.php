<?php

namespace Smart\SonataBundle\Logger;

use Doctrine\ORM\EntityManagerInterface;
use Sentry\ClientInterface;
use Smart\SonataBundle\Entity\Log\BatchLog;

/**
 * @deprecated use Smart\CoreBundle\Monitor\ProcessMonitor instead
 *
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 */
class BatchLogger
{
    private EntityManagerInterface $entityManager;
    private ClientInterface $sentry;

    public function __construct(EntityManagerInterface $entityManager, ClientInterface $sentry)
    {
        $this->entityManager = $entityManager;
        $this->sentry = $sentry;
    }

    public function log(array $parameters): void
    {
        if (isset($parameters['exception'])) {
            $this->sentry->captureException($parameters['exception']);
        }

        $batchLog = new BatchLog();
        if (!isset($parameters['context'])) {
            throw new \Exception("'context' must be present in batch log");
        }
        $batchLog->setContext($parameters['context']);

        if (!isset($parameters['name'])) {
            throw new \Exception("'name' must be present in batch log");
        }
        $batchLog->setName($parameters['name']);

        if (isset($parameters['summary'])) {
            $batchLog->setSummary($parameters['summary']);
        }

        if (isset($parameters['comment'])) {
            $batchLog->setComment($parameters['comment']);
        }

        if (isset($parameters['success'])) {
            $batchLog->setSuccess($parameters['success']);
        }

        if (isset($parameters['data'])) {
            $batchLog->setData($parameters['data']);
        }

        $this->entityManager->persist($batchLog);
        $this->entityManager->flush();
    }
}
