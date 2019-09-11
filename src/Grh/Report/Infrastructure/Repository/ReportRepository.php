<?php
namespace CTIC\Grh\Report\Infrastructure\Repository;

use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Report\Domain\ReportFilter;

class ReportRepository extends EntityRepository
{
    /**
     * @param ReportFilter $reportFilter
     *
     * @return mixed
     */
    public function findTotalHoursByEmployee(ReportFilter $reportFilter)
    {
        $fromDate = $reportFilter->fromDate;
        $toDate = $reportFilter->toDate;

        $qb = $this->_em->createQueryBuilder()
            ->select('e')
            ->from('CTIC\Grh\Event\Domain\Event', 'e')
            ->where('e.fromDate >= :fromDate')
            ->andWhere('e.toDate <= :toDate')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->orderBy('e.fromDate', 'ASC')
            ->getQuery()
        ;

        return $qb->execute();
    }
}