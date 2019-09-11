<?php
namespace CTIC\Grh\Employee\Infrastructure\Repository;

use CTIC\Grh\Employee\Domain\Employee;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;

class EmployeeRepository extends EntityRepository
{
    /**
     * @return Employee[]
     */
    public function findAllOrderedByName(): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @return Employee
     *
     * @throws
     */
    public function findOneRandom(): Employee
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC')
            ->getQuery();

        /** @var Employee $employee */
        $employee = $qb->setMaxResults(1)->getOneOrNullResult();

        return $employee;
    }
}