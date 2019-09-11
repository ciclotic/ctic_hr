<?php
namespace CTIC\Grh\Employee\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Domain\IdentifiableInterface;

interface EmployeeAttachedInterface extends IdentifiableInterface, EntityInterface
{
    const ATTACHED_PATH = '/img/employee';
    public function getAttached();
    public function getEmployee();
}