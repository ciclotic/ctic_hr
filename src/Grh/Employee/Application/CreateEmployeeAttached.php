<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeeAreaCommand;
use CTIC\Grh\Employee\Domain\Command\EmployeeAttachedCommand;
use CTIC\Grh\Employee\Domain\EmployeeArea;
use CTIC\Grh\Employee\Domain\EmployeeAttached;

class CreateEmployeeAttached implements CreateInterface
{
    /**
     * @param CommandInterface|EmployeeAttachedCommand $command
     * @return EntityInterface|EmployeeAttached
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $employeeAttached = new EmployeeAttached();
        $employeeAttached->setAttached($command->attached);
        $employeeAttached->employee = $command->employee;

        return $employeeAttached;
    }
}