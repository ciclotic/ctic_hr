<?php
namespace CTIC\Grh\Employee\Domain\Fixture;

use CTIC\Grh\Employee\Application\CreateEmployeeArea;
use CTIC\App\User\Infrastructure\Repository\UserRepository;
use CTIC\App\User\Domain\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use CTIC\Grh\Employee\Domain\Command\EmployeeAreaCommand;

class EmployeeAreaFixture extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $employeeCommandProfesor = new EmployeeAreaCommand();
        $employeeCommandProfesor->name = 'Profesor';
        $employeeProfesor = CreateEmployeeArea::create($employeeCommandProfesor);
        $manager->persist($employeeProfesor);

        $employeeCommandConcierto = new EmployeeAreaCommand();
        $employeeCommandConcierto->name = 'Concierto';
        $employeeConcierto = CreateEmployeeArea::create($employeeCommandConcierto);
        $manager->persist($employeeConcierto);

        $employeeCommandDiscoteca = new EmployeeAreaCommand();
        $employeeCommandDiscoteca->name = 'Discoteca';
        $employeeDiscoteca = CreateEmployeeArea::create($employeeCommandDiscoteca);
        $manager->persist($employeeDiscoteca);

        $manager->flush();
    }
}