<?php
namespace CTIC\Grh\Employee\Infrastructure\Repository;

use CTIC\Grh\Employee\Domain\EmployeeCategory;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;

class EmployeeCategoryRepository extends EntityRepository
{
    /**
     * @return EmployeeCategory[]
     */
    public function findAllOrderedByName(): array
    {
        $qb = $this->createQueryBuilder('ec')
            ->orderBy('ec.name', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @return EmployeeCategory
     *
     * @throws
     */
    public function findOneRandom(): EmployeeCategory
    {
        $qb = $this->createQueryBuilder('ec')
            ->orderBy('ec.name', 'ASC')
            ->getQuery();

        /** @var EmployeeCategory $employeeCategory */
        $employeeCategory = $qb->setMaxResults(1)->getOneOrNullResult();

        return $employeeCategory;
    }
}