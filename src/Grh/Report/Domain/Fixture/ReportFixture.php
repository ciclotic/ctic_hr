<?php
namespace CTIC\Grh\Report\Domain\Fixture;

use CTIC\Grh\Report\Application\CreateReport;
use CTIC\Grh\Report\Domain\Command\ReportCommand;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class ReportFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $reportCommandDefault = new ReportCommand();
        $reportCommandDefault->name = 'default';
        $reportDefault = CreateReport::create($reportCommandDefault);
        $manager->persist($reportDefault);

        $manager->flush();
    }
}