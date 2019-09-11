<?php
namespace CTIC\Grh\Report\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;

class ReportCommand implements CommandInterface
{
    /**
     * @var string
     */
    public $name;
}