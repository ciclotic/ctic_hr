<?php
namespace CTIC\Grh\Event\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Event\Domain\Event;

class EventEmployeeCommand implements CommandInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var Employee
     */
    public $employee;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var int
     */
    public $turn;
}