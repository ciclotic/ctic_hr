<?php
namespace CTIC\Grh\Employee\Infrastructure\Repository;

use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\EmployeePersonalAffairs;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Report\Domain\ReportFilter;

class EmployeePersonalAffairsRepository extends EntityRepository
{
    /**
     * @return EmployeePersonalAffairs[]
     */
    public function findAllOrderedByName(): array
    {
        $qb = $this->createQueryBuilder('epa')
            ->orderBy('epa.name', 'ASC')
            ->getQuery();

        return $qb->exeaute();
    }

    /**
     * @return EmployeePersonalAffairs
     *
     * @throws
     */
    public function findOneRandom(): EmployeePersonalAffairs
    {
        $qb = $this->createQueryBuilder('epa')
            ->orderBy('epa.name', 'ASC')
            ->getQuery();

        /** @var EmployeePersonalAffairs $employeePersonalAffairs */
        $employeePersonalAffairs = $qb->setMaxResults(1)->getOneOrNullResult();

        return $employeePersonalAffairs;
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return EmployeePersonalAffairs[]|null
     *
     * @throws
     */
    public function findByRangeDate(ReportFilter $reportFilter = null)
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

        $qb = $this->createQueryBuilder('epa')
            ->where('epa.fromDate >= :fromDate')
            ->andWhere('epa.toDate <= :toDate')
            ->orderBy('epa.toDate', 'ASC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->getQuery()
        ;

        return $qb->execute();
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return int
     *
     * @throws
     */
    public function findLastEmployeeIdByRangeDate(ReportFilter $reportFilter = null)
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

        $qb = $this->createQueryBuilder('epa')
            ->join('epa.employee', 'e')
            ->where('epa.fromDate >= :fromDate')
            ->andWhere('epa.toDate <= :toDate')
            ->orderBy('e.id', 'DESC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->getQuery()
        ;

        /** @var EmployeePersonalAffairs $employeePersonalAffairs */
        $employeePersonalAffairs = $qb->setMaxResults(1)->getOneOrNullResult();

        if (is_object($employeePersonalAffairs) && get_class($employeePersonalAffairs) == EmployeePersonalAffairs::class) {
            return $employeePersonalAffairs->getEmployee()->getId();
        }

        return 0;
    }

    /**
     * @param ReportFilter $reportFilter
     *
     * @return \DateTime|null
     *
     * @throws
     */
    public function findLastEmployeePersonalAffairsToDateByRangeDate(ReportFilter $reportFilter = null)
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

        $qb = $this->createQueryBuilder('epa')
            ->where('epa.fromDate >= :fromDate')
            ->andWhere('epa.toDate <= :toDate')
            ->orderBy('epa.toDate', 'DESC')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->getQuery()
        ;

        /** @var EmployeePersonalAffairs $employeePersonalAffairs */
        $employeePersonalAffairs = $qb->setMaxResults(1)->getOneOrNullResult();

        if (is_object($employeePersonalAffairs) && get_class($employeePersonalAffairs) == EmployeePersonalAffairs::class) {
            return $employeePersonalAffairs->getToDate();
        }

        return null;
    }
}