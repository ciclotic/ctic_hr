<?php
namespace CTIC\Grh\Employee\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\User\Domain\User;
use CTIC\App\Base\Domain\IdentifiableInterface;

interface EmployeePersonalAffairsInterface extends IdentifiableInterface, EntityInterface
{

    public function getEmployee();
    public function setEmployee(Employee $employee): bool;
    public function getFromDate(): \DateTime;
    public function getToDate(): \DateTime;
    public function getUserCreator(): User;
    public function setUserCreator(User $user): bool;
}