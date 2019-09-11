<?php
namespace CTIC\Grh\Employee\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\Grh\Employee\Domain\Employee;

class EmployeeAttachedCommand implements CommandInterface
{
    /**
     * @var string
     */
    public $attached;

    /**
     * @var Employee
     */
    public $employee;
}