<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeeAreaCommand;
use CTIC\Grh\Employee\Domain\EmployeeArea;

class CreateEmployeeArea implements CreateInterface
{
    /**
     * @param CommandInterface|EmployeeAreaCommand $command
     * @return EntityInterface|EmployeeArea
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $employeeArea = new EmployeeArea();
        $employeeArea->name = $command->name;
        if (!empty($command->userCreator)) {
            $employeeArea->setUserCreator($command->userCreator);
        }

        return $employeeArea;
    }
}