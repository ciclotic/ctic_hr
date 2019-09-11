<?php
namespace CTIC\Grh\Employee\Infrastructure\Repository;

use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\EmployeeLow;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Report\Domain\ReportFilter;

class EmployeeLowRepository extends EntityRepository
{
    /**
     * @return EmployeeLow[]
     */
    public function findAllOrderedByName(): array
    {
        $qb = $this->createQueryBuilder('el')
            ->orderBy('el.name', 'ASC')
            ->getQuery();

        return $qb->exeaute();
    }

    /**
     * @return EmployeeLow
     *
     * @throws
     */
    public function findOneRandom(): EmployeeLow
    {
        $qb = $this->createQueryBuilder('el')
            ->orderBy('el.name', 'ASC')
            ->getQuery();

        /** @var EmployeeLow $employeeLow */
        $employeeLow = $qb->setMaxResults(1)->getOneOrNullResult();

        return $employeeLow;
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return EmployeeLow[]|null
     *
     */
    public function findByRangeDate(ReportFilter $reportFilter)
    {
        $fromDate = $reportFilter->fromDate;
        $toDate = $reportFilter->toDate;

        $qb = $this->createQueryBuilder('el')
            ->where('el.fromDate >= :fromDate')
            ->andWhere('el.toDate <= :toDate')
            ->orderBy('el.fromDate', 'ASC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->getQuery()
        ;

        return $qb->execute();
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return int|null
     *
     * @throws
     */
    public function findLastEmployeeIdByRangeDate(ReportFilter $reportFilter)
    {
        $fromDate = $reportFilter->fromDate;

        $qb = $this->createQueryBuilder('el')
            ->join('el.employee', 'e')
            ->where('el.fromDate >= :fromDate')
            ->orderBy('e.id', 'DESC')
            ->setParameter('fromDate', $fromDate)
            ->getQuery()
        ;

        /** @var EmployeeLow $employeeLow */
        $employeeLow = $qb->setMaxResults(1)->getOneOrNullResult();

        if (is_object($employeeLow) && get_class($employeeLow) == EmployeeLow::class) {
            return $employeeLow->getEmployee()->getId();
        }

        return null;
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return string|null
     *
     * @throws
     */
    public function findLastEmployeeLowToDateByRangeDate(ReportFilter $reportFilter)
    {
        $fromDate = $reportFilter->fromDate;

        $qb = $this->createQueryBuilder('el')
            ->where('el.fromDate >= :fromDate')
            ->orderBy('el.toDate', 'DESC')
            ->setParameter('fromDate', $fromDate)
            ->getQuery()
        ;

        /** @var EmployeeLow $employeeLow */
        $employeeLow = $qb->setMaxResults(1)->getOneOrNullResult();

        if (is_object($employeeLow) && get_class($employeeLow) == EmployeeLow::class) {
            return $employeeLow->getToDate()->format('Y-m-d');
        }

        return null;
    }
}