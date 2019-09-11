<?php
namespace CTIC\Grh\Employee\Domain\Fixture;

use CTIC\Grh\Employee\Application\CreateEmployeeCategory;
use CTIC\App\User\Infrastructure\Repository\UserRepository;
use CTIC\App\User\Domain\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CTIC\Grh\Employee\Domain\Command\EmployeeCategoryCommand;

class EmployeeCategoryFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $employeeCommandBarman = new EmployeeCategoryCommand();
        $employeeCommandBarman->name = 'Barman';
        $employeeBarman = CreateEmployeeCategory::create($employeeCommandBarman);
        $manager->persist($employeeBarman);

        $employeeCommandDj = new EmployeeCategoryCommand();
        $employeeCommandDj->name = 'Dj';
        $employeeDj = CreateEmployeeCategory::create($employeeCommandDj);
        $manager->persist($employeeDj);

        $employeeCommandSecurity = new EmployeeCategoryCommand();
        $employeeCommandSecurity->name = 'Security';
        $employeeSecurity = CreateEmployeeCategory::create($employeeCommandSecurity);
        $manager->persist($employeeSecurity);

        $manager->flush();
    }
}