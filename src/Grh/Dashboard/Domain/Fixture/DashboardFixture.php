<?php
namespace CTIC\Grh\Dashboard\Domain\Fixture;

use CTIC\Grh\Dashboard\Application\CreateDashboard;
use CTIC\Grh\Dashboard\Domain\Command\DashboardCommand;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class DashboardFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $dashboardCommandDefault = new DashboardCommand();
        $dashboardCommandDefault->name = 'default';
        $dashboardDefault = CreateDashboard::create($dashboardCommandDefault);
        $manager->persist($dashboardDefault);

        $manager->flush();
    }
}