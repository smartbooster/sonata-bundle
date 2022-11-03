<?php

namespace Smart\SonataBundle\Repository\Log;

use Doctrine\ORM\EntityRepository;
use Smart\SonataBundle\Entity\Log\BatchLog;

/**
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 *
 * @extends \Doctrine\ORM\EntityRepository<BatchLog>
 */
class BatchLogRepository extends EntityRepository
{
    public function getAllContexts(): array
    {
        return $this->createQueryBuilder('b_l')
            ->select('b_l.context')
            ->groupBy('b_l.context')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function getAllNames(): array
    {
        return $this->createQueryBuilder('b_l')
            ->select('b_l.name')
            ->groupBy('b_l.name')
            ->getQuery()
            ->getSingleColumnResult()
            ;
    }
}
