<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeeCommand;
use CTIC\Grh\Employee\Domain\Employee;

class CreateEmployee implements CreateInterface
{
    /**
     * @param CommandInterface|EmployeeCommand $command
     * @return EntityInterface|Employee
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $employee = new Employee();
        $employee->name = $command->name;
        if (!empty($command->userCreator)) {
            $employee->setUserCreator($command->userCreator);
        }

        return $employee;
    }
}