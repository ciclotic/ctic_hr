<?php
namespace CTIC\Grh\Report\Application;

use CTIC\App\Base\Application\CreateInterface;
use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\Grh\Report\Domain\Command\ReportCommand;
use CTIC\Grh\Report\Domain\Report;

class CreateReport implements CreateInterface
{
    /**
     * @param CommandInterface|ReportCommand $command
     * @return EntityInterface|Report
     */
    public static function create(CommandInterface $command): EntityInterface
    {
        $report = new Report();
        $report->name = $command->name;

        return $report;
    }
}