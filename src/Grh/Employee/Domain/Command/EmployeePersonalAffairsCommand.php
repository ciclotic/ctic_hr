<?php
namespace CTIC\Grh\Employee\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\App\User\Domain\User;

class EmployeePersonalAffairsCommand implements CommandInterface
{
    /**
     * @var User
     */
    public $userCreator;

    /**
     * @var Employee
     */
    public $employee;
}