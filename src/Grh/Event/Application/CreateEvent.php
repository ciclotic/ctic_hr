<?php
namespace CTIC\Grh\Event\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Event\Domain\Command\EventCommand;
use CTIC\Grh\Event\Domain\Event;

class CreateEvent implements CreateInterface
{
    /**
     * @param CommandInterface|EventCommand $command
     * @return EntityInterface|Event
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $event = new Event();
        $event->setId($command->id);
        $event->name = (empty($command->name))? '' : $command->name;
        $event->description = (empty($command->description))? '' : $command->description;

        return $event;
    }
}