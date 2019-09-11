<?php
namespace CTIC\Grh\Fichar\Infrastructure\Repository;

use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Fichar\Domain\Fichar;
use CTIC\Grh\Report\Domain\ReportFilter;

class FicharRepository extends EntityRepository
{
    /**
     * @param ReportFilter $reportFilter
     *
     * @return Fichar[]|null
     * @throws
     *
     */
    public function findByRangeDate(ReportFilter $reportFilter = null, $orderDirection = 'ASC')
    {
        if ($reportFilter === null) {
            $month = date('n');
            $year = date('Y');
            if ($month == '1') {
                $month = '12';
                $year = $year - 1;
            } else {
                $month = $month - 1;
            }
            if (strlen($month) < 2) {
                $month = '0' . $month;
            }
            $fromDate = new \DateTime($year . '-' . $month . '-' . '01');
            $toDate = clone $fromDate;
            $toDate->modify('+30 days');
        } else {
            $fromDate = $reportFilter->fromDate;
            $toDate = $reportFilter->toDate;
        }

        $qb = $this->createQueryBuilder('f')
            ->where('f.date >= :fromDate')
            ->andWhere('f.date <= :toDate')
            ->orderBy('f.date', $orderDirection)
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->getQuery()
        ;

        return $qb->execute();
    }
}