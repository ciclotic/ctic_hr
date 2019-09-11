<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeePersonalAffairsCommand;
use CTIC\Grh\Employee\Domain\EmployeePersonalAffairs;

class CreateEmployeePersonalAffairs implements CreateInterface
{
    /**
     * @param CommandInterface|EmployeePersonalAffairsCommand $command
     * @return EntityInterface|EmployeePersonalAffairs
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $employeePersonalAffairs = new EmployeePersonalAffairs();
        if (!empty($command->employee)) {
            $employeePersonalAffairs->setEmployee($command->employee);
        }
        if (!empty($command->userCreator)) {
            $employeePersonalAffairs->setUserCreator($command->userCreator);
        }

        return $employeePersonalAffairs;
    }
}