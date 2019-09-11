<?php
namespace CTIC\Grh\Fichar\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\Grh\Employee\Domain\Employee;

class FicharCommand implements CommandInterface
{
    /**
     * @var \DateTime
     */
    public $date;

    /**
     * @var int
     */
    public $action;

    /**
     * @var string
     */
    public $latitude;

    /**
     * @var string
     */
    public $longitude;

    /**
     * @var Employee
     */
    public $employee;
}