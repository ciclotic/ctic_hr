<?php
namespace CTIC\Grh\Employee\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\User\Domain\User;
use CTIC\App\Base\Domain\IdentifiableInterface;
use Doctrine\Common\Collections\ArrayCollection;

interface EmployeeInterface extends IdentifiableInterface, EntityInterface
{
    const CONTRACT_TYPE_INTERN = 0;
    const CONTRACT_TYPE_SUB = 1;

    const WORKING_DAY_FIX = 0;
    const WORKING_DAY_FLEX = 1;

    const PRICE_PER_HOUR_FIX = 0;
    const PRICE_PER_HOUR_FLEX = 1;

    public function getName(): string;
    public function getSurname(): string;
    public function getDni(): string;
    public function getSocialSecurity(): string;
    public function getAccountNumber(): string;
    public function getPhone(): string;
    public function getAddress(): string;
    public function getEmail(): string;
    public function getContractType(): int;
    public function setContractType($contractType): bool;
    public function getWorkingDayType(): int;
    public function setWorkingDayType($workingDayType): bool;
    public function getCostPerHour(): float;
    public function getPricePerHour(): float;
    public function getPricePerHourType(): int;
    public function setPricePerHourType($pricePerHourType): bool;
    public function getBarcode(): int;
    public function isEnabled(): bool;

    public function getUserCreator(): User;
    public function setUserCreator(User $user): bool;

    public function getUserRelated(): ?User;
    public function setUserRelated($user): bool;

    public function getEmployeeCategory();
    public function setEmployeeCategory($employeeCategory): bool;

    public function getEmployeeArea();
    public function setEmployeeArea($employeeArea): bool;

    public function getAttached();
    public function setAttached($attached): bool;

    public function getEmployeeLow();
    public function setEmployeeLow($employeeLow): bool;

    public function getEmployeePersonalAffairs();
    public function setEmployeePersonalAffairs($employeePersonalAffairs): bool;
}