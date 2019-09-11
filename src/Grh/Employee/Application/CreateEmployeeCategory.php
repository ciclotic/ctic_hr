<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeeCategoryCommand;
use CTIC\Grh\Employee\Domain\EmployeeCategory;

class CreateEmployeeCategory implements CreateInterface
{
    /**
     * @param CommandInterface|EmployeeCategoryCommand $command
     * @return EntityInterface|EmployeeCategory
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $employeeCategory = new EmployeeCategory();
        $employeeCategory->name = $command->name;
        if (!empty($command->userCreator)) {
            $employeeCategory->setUserCreator($command->userCreator);
        }

        return $employeeCategory;
    }
}