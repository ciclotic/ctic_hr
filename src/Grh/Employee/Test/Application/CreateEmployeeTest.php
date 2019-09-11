<?php
declare(strict_types=1);

namespace CTIC\Grh\Employee\Test\Application;

use CTIC\Grh\Employee\Application\CreateEmployee;
use CTIC\Grh\Employee\Domain\Command\EmployeeCommand;
use CTIC\Grh\Employee\Domain\Employee;
use PHPUnit\Framework\TestCase;

final class CreateEmployeeTest extends TestCase
{
    public function testCreateAssert()
    {
        $employeeCommandRyu = new EmployeeCommand();
        $employeeCommandRyu->name = 'ryu';
        $employeeRyu = CreateEmployee::create($employeeCommandRyu);

        $this->assertEquals(Employee::class, get_class($employeeRyu));
    }
}