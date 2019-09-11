<?php
namespace CTIC\Grh\Employee\Domain\Fixture;

use CTIC\Grh\Employee\Application\CreateEmployeeArea;
use CTIC\App\User\Infrastructure\Repository\UserRepository;
use CTIC\App\User\Domain\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CTIC\Grh\Employee\Domain\Command\EmployeeAreaCommand;

class EmployeePersonalAffairsFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        // TODO
    }
}