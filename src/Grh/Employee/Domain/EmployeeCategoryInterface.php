<?php
namespace CTIC\Grh\Employee\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\User\Domain\User;
use CTIC\App\Base\Domain\IdentifiableInterface;

interface EmployeeCategoryInterface extends IdentifiableInterface, EntityInterface
{
    public function getName(): string;

    public function getUserCreator(): User;
    public function setUserCreator(User $user): bool;
}