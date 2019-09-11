<?php
namespace CTIC\Grh\Employee\Domain;

use CTIC\Grh\Employee\Domain\Validation\EmployeeLowValidation;
use Doctrine\ORM\Mapping as ORM;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\App\User\Domain\User;

/**
 * @ORM\Entity(repositoryClass="CTIC\Grh\Employee\Infrastructure\Repository\EmployeeLowRepository")
 */
class EmployeeLow implements EmployeeLowInterface
{
    use IdentifiableTrait;
    use EmployeeLowValidation;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\Grh\Employee\Domain\Employee", inversedBy="employeeLow")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id", nullable=true)
     *
     * @var Employee
     */
    private $employee;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    public $fromDate;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    public $toDate;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\App\User\Domain\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @var User
     */
    private $userCreator;

    /**
     * @return Employee|null
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
     *
     * @return bool
     */
    public function setEmployee(Employee $employee): bool
    {
        if (get_class($employee) != Employee::class) {
            return false;
        }

        $this->employee = $employee;

        return true;
    }

    /**
     * @return \DateTime
     */
    public function getFromDate(): \DateTime
    {
        return (empty($this->fromDate))? new \DateTime() : $this->fromDate;
    }

    /**
     * @return \DateTime
     */
    public function getToDate(): \DateTime
    {
        return (empty($this->toDate))? new \DateTime() : $this->toDate;
    }

    /**
     * @return User
     */
    public function getUserCreator(): User
    {
        return $this->userCreator;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function setUserCreator(User $user): bool
    {
        if (get_class($user) != User::class) {
            return false;
        }

        $this->userCreator = $user;

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }
}