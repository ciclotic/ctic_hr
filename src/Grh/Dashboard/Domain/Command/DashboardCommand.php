<?php
namespace CTIC\Grh\Dashboard\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;

class DashboardCommand implements CommandInterface
{
    /**
     * @var string
     */
    public $name;
}