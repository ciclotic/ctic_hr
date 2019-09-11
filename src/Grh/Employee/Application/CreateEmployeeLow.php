<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeeLowCommand;
use CTIC\Grh\Employee\Domain\EmployeeLow;

class CreateEmployeeLow implements CreateInterface
{
    /**
     * @param CommandInterface|EmployeeLowCommand $command
     * @return EntityInterface|EmployeeLow
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $employeeLow = new EmployeeLow();
        if (!empty($command->employee)) {
            $employeeLow->setEmployee($command->employee);
        }
        if (!empty($command->userCreator)) {
            $employeeLow->setUserCreator($command->userCreator);
        }

        return $employeeLow;
    }
}