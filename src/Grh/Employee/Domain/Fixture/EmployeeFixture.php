<?php
namespace CTIC\Grh\Employee\Domain\Fixture;

use CTIC\App\User\Infrastructure\Repository\UserRepository;
use CTIC\App\User\Domain\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CTIC\Grh\Employee\Application\CreateEmployee;
use CTIC\Grh\Employee\Domain\Command\EmployeeCommand;

class EmployeeFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        //TODO complete required fields
        $employeeCommandValenti = new EmployeeCommand();
        $employeeCommandValenti->name = 'felipe';
        $employeeValenti = CreateEmployee::create($employeeCommandValenti);
        $manager->persist($employeeValenti);

        $employeeCommandToni = new EmployeeCommand();
        $employeeCommandToni->name = 'jose';
        $employeeToni= CreateEmployee::create($employeeCommandToni);
        $manager->persist($employeeToni);

        $employeeCommandDani = new EmployeeCommand();
        $employeeCommandDani->name = 'carla';
        $employeeDani = CreateEmployee::create($employeeCommandDani);
        $manager->persist($employeeDani);

        $manager->flush();
    }
}