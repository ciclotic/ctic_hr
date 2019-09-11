<?php
namespace CTIC\Grh\Event\Infrastructure\Repository;

use CTIC\Grh\Event\Domain\Event;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Event\Domain\EventEmployee;

class EventEmployeeRepository extends EntityRepository
{
    /**
     * @return EventEmployee[]
     */
    public function findAllOrderedById(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @return EventEmployee
     *
     * @throws
     */
    public function findOneRandom(): Event
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        /** @var Event $event */
        $event = $qb->setMaxResults(1)->getOneOrNullResult();

        return $event;
    }
}