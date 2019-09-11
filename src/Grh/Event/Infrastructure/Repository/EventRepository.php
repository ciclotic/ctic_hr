<?php
namespace CTIC\Grh\Event\Infrastructure\Repository;

use CTIC\Grh\Event\Domain\Event;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;

class EventRepository extends EntityRepository
{
    /**
     * @return Event[]
     */
    public function findAllOrderedByName(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @return Event[]
     */
    public function findFromCurrentOrderedByDate(): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.fromDate >= :now')
            ->orderBy('e.fromDate', 'ASC')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery();

        return $qb->setMaxResults(10)->execute();
    }

    /**
     * @return Event[]
     */
    public function findByIndexFilter($criteria, $sorting, $limit): array
    {
        $qb = $this->createQueryBuilder('e');

        $firstWhere = true;
        if (!empty($criteria['fromDate'])) {
            if ($firstWhere == true) {
                $firstWhere = false;
                $qb = $qb->where('e.fromDate >= :fromDate');
            } else {
                $qb = $qb->andWhere('e.fromDate >= :fromDate');
            }
        }
        if (!empty($criteria['id'])) {
            if ($firstWhere == true) {
                $firstWhere = false;
                $qb = $qb->where('e.id LIKE :id');
            } else {
                $qb = $qb->andWhere('e.id LIKE :id');
            }
        }
        if (!empty($criteria['name'])) {
            if ($firstWhere == true) {
                $firstWhere = false;
                $qb = $qb->where('e.name LIKE :name');
            } else {
                $qb = $qb->andWhere('e.name LIKE :name');
            }
        }

        foreach ($sorting as $property => $direction) {
            $qb->orderBy("e.$property", $direction);
        }

        (empty($criteria['id']))?: $qb->setParameter('id', $criteria['id']);
        (empty($criteria['name']))?: $qb->setParameter('name', $criteria['name']);
        (empty($criteria['fromDate']))?: $qb->setParameter('fromDate', $criteria['fromDate']);

        $qb = $qb->getQuery();

        if (!empty($limit)) {
            return $qb->setMaxResults($limit)->execute();
        }

        return $qb->execute();
    }

    /**
     * @return Event
     *
     * @throws
     */
    public function findOneRandom(): Event
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.name', 'ASC')
            ->getQuery();

        /** @var Event $event */
        $event = $qb->setMaxResults(1)->getOneOrNullResult();

        return $event;
    }
}