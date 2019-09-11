<?php
namespace CTIC\Grh\Event\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Event\Domain\Command\EventEmployeeCommand;
use CTIC\Grh\Event\Domain\EventEmployee;

class CreateEventEmployee implements CreateInterface
{
    /**
     * @param CommandInterface|EventEmployeeCommand $command
     * @return EntityInterface|EventEmployee
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $event = new EventEmployee();
        $event->setId($command->id);
        $event->turn = (empty($command->turn))? 0 : $command->turn;
        if (!empty($command->employee) && get_class($command->employee) == Employee::class) {
            $event->setEmployee($command->employee);
        }

        return $event;
    }
}