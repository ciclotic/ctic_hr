<?php
namespace CTIC\Grh\Employee\Infrastructure\Repository;

use CTIC\Grh\Employee\Domain\EmployeeArea;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;

class EmployeeAreaRepository extends EntityRepository
{
    /**
     * @return EmployeeArea[]
     */
    public function findAllOrderedByName(): array
    {
        $qb = $this->createQueryBuilder('ea')
            ->orderBy('ea.name', 'ASC')
            ->getQuery();

        return $qb->exeaute();
    }

    /**
     * @return EmployeeArea
     *
     * @throws
     */
    public function findOneRandom(): EmployeeArea
    {
        $qb = $this->createQueryBuilder('ea')
            ->orderBy('ea.name', 'ASC')
            ->getQuery();

        /** @var EmployeeArea $employeeArea */
        $employeeArea = $qb->setMaxResults(1)->getOneOrNullResult();

        return $employeeArea;
    }
}