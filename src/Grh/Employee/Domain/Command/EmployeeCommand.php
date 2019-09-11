<?php
namespace CTIC\Grh\Employee\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\User\Domain\User;

class EmployeeCommand implements CommandInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var User
     */
    public $userCreator;
}