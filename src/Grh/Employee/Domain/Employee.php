<?php
namespace CTIC\Grh\Employee\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Employee\Domain\Validation\EmployeeValidation;
use CTIC\App\User\Domain\User;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="CTIC\Grh\Employee\Infrastructure\Repository\EmployeeRepository")
 */
class Employee implements EmployeeInterface
{
    use IdentifiableTrait;
    use EmployeeValidation;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $name;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $surname;

    /**
     * @ORM\Column(type="string", length=20)
     *
     * @var string
     */
    public $dni;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $socialSecurity;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $accountNumber;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $phone;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $address;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $email;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    public $contractType = 0;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    public $workingDayType = 0;

    /**
     * @ORM\Column(type="float", length=10)
     *
     * @var float
     */
    public $costPerHour;

    /**
     * @ORM\Column(type="float", length=10)
     *
     * @var float
     */
    public $pricePerHour;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    public $pricePerHourType = 0;

    /**
     * @ORM\Column(type="integer", unique=true, length=10)
     *
     * @var integer
     */
    public $barcode;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    public $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\App\User\Domain\User")
     * @ORM\JoinColumn(name="user_related_id", referencedColumnName="id", nullable=true)
     *
     * @var null|User
     */
    private $userRelated;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\App\User\Domain\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *
     * @var User
     */
    private $userCreator;

    /**
     * @ORM\ManyToMany(targetEntity="CTIC\Grh\Employee\Domain\EmployeeCategory")
     *
     * @var EmployeeCategory[]
     */
    private $employeeCategory;

    /**
     * @ORM\ManyToMany(targetEntity="CTIC\Grh\Employee\Domain\EmployeeArea")
     *
     * @var EmployeeCategory[]
     */
    private $employeeArea;

    /**
     * @ORM\OneToMany(targetEntity="CTIC\Grh\Employee\Domain\EmployeeAttached", mappedBy="employee", cascade={"persist", "remove"})
     *
     * @var EmployeeAttached[]
     */
    private $attached;

    /**
     * @ORM\OneToMany(targetEntity="CTIC\Grh\Employee\Domain\EmployeeLow", mappedBy="employee")
     *
     * @var EmployeeLow[]
     */
    private $employeeLow;

    /**
     * @ORM\OneToMany(targetEntity="CTIC\Grh\Employee\Domain\EmployeePersonalAffairs", mappedBy="employee")
     *
     * @var EmployeePersonalAffairs[]
     */
    private $employeePersonalAffairs;

    /**
     * Employee constructor.
     */
    public function __construct()
    {
        $this->employeeCategory = new ArrayCollection();
        $this->employeeArea = new ArrayCollection();
        $this->attached = new ArrayCollection();
        $this->employeeLow = new ArrayCollection();
        $this->employeePersonalAffairs = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (empty($this->name))? '' : $this->name;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return (empty($this->surname))? '' : $this->surname;
    }

    /**
     * @return string
     */
    public function getDni(): string
    {
        return (empty($this->dni))? '' : $this->dni;
    }

    /**
     * @return string
     */
    public function getSocialSecurity(): string
    {
        return (empty($this->socialSecurity))? '' : $this->socialSecurity;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return (empty($this->accountNumber))? '' : $this->accountNumber;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return (empty($this->phone))? '' : $this->phone;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return (empty($this->address))? '' : $this->address;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return (empty($this->email))? '' : $this->email;
    }

    /**
     * @return int
     */
    public function getContractType(): int
    {
        return (empty($this->contractType))? self::CONTRACT_TYPE_INTERN : $this->contractType;
    }

    /**
     * @param $contractType
     * @return bool
     */
    public function setContractType($contractType): bool
    {
        if ($contractType == self::CONTRACT_TYPE_INTERN || $contractType == self::CONTRACT_TYPE_SUB)
        {
            $this->contractType = $contractType;

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getWorkingDayType(): int
    {
        return (empty($this->workingDayType))? self::WORKING_DAY_FIX : $this->workingDayType;
    }

    /**
     * @param $workingDayType
     * @return bool
     */
    public function setWorkingDayType($workingDayType): bool
    {
        if ($workingDayType == self::WORKING_DAY_FIX || $workingDayType == self::WORKING_DAY_FLEX)
        {
            $this->workingDayType = $workingDayType;

            return true;
        }

        return false;
    }

    /**
     * @return float
     */
    public function getCostPerHour(): float
    {
        return (empty($this->costPerHour))? 0 : $this->costPerHour;
    }

    /**
     * @return float
     */
    public function getPricePerHour(): float
    {
        return (empty($this->pricePerHour))? 0 : $this->pricePerHour;
    }

    /**
     * @return int
     */
    public function getPricePerHourType(): int
    {
        return (empty($this->pricePerHourType))? self::PRICE_PER_HOUR_FIX : $this->pricePerHourType;
    }

    /**
     * @param $pricePerHourType
     * @return bool
     */
    public function setPricePerHourType($pricePerHourType): bool
    {
        if ($pricePerHourType == self::PRICE_PER_HOUR_FIX || $pricePerHourType == self::PRICE_PER_HOUR_FLEX)
        {
            $this->pricePerHourType = $pricePerHourType;

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getBarcode(): int
    {
        return (empty($this->barcode))? 0 : $this->barcode;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return ($this->enabled)? true : false;
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
     * @return null|User
     */
    public function getUserRelated(): ?User
    {
        return $this->userRelated;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function setUserRelated($user): bool
    {
        if (get_class($user) != User::class) {
            if (empty($user)) {
                $this->userRelated = null;

                return true;
            }

            return false;
        }

        $this->userRelated = $user;

        return true;
    }

    /**
     * @return EmployeeCategory[]|ArrayCollection
     */
    public function getEmployeeCategory()
    {
        return $this->employeeCategory;
    }

    /**
     * @param $employeeCategory
     * @return bool
     */
    public function setEmployeeCategory($employeeCategory): bool
    {
        if (get_class($employeeCategory) != ArrayCollection::class) {
            return false;
        }

        $this->employeeCategory = $employeeCategory;

        return true;
    }

    /**
     * @return EmployeeArea[]|ArrayCollection
     */
    public function getEmployeeArea()
    {
        return $this->employeeArea;
    }

    /**
     * @param $employeeArea
     * @return bool
     */
    public function setEmployeeArea($employeeArea): bool
    {
        if (get_class($employeeArea) != ArrayCollection::class) {
            return false;
        }

        $this->employeeArea = $employeeArea;

        return true;
    }

    /**
     * @return EmployeeAttached[]|ArrayCollection
     */
    public function getAttached()
    {
        return $this->attached;
    }

    /**
     * @param $employeeAttached
     * @return bool
     */
    public function setAttached($employeeAttached): bool
    {
        if (get_class($employeeAttached) != ArrayCollection::class) {
            return false;
        }

        $this->attached = $employeeAttached;

        return true;
    }

    /**
     * @return EmployeeLow[]|ArrayCollection
     */
    public function getEmployeeLow()
    {
        return $this->employeeLow;
    }

    /**
     * @param $employeeLow
     * @return bool
     */
    public function setEmployeeLow($employeeLow): bool
    {
        if (get_class($employeeLow) != ArrayCollection::class) {
            return false;
        }

        $this->employeeLow = $employeeLow;

        return true;
    }

    /**
     * @return EmployeePersonalAffairs[]|ArrayCollection
     */
    public function getEmployeePersonalAffairs()
    {
        return $this->employeePersonalAffairs;
    }

    /**
     * @param $employeePersonalAffairs
     * @return bool
     */
    public function setEmployeePersonalAffairs($employeePersonalAffairs): bool
    {
        if (get_class($employeePersonalAffairs) != ArrayCollection::class) {
            return false;
        }

        $this->employeePersonalAffairs = $employeePersonalAffairs;

        return true;
    }
}