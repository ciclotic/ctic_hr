<?php
namespace CTIC\Grh\Fichar\Domain;

use CTIC\Grh\Employee\Domain\Employee;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Fichar\Domain\Validation\FicharValidation;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="CTIC\Grh\Fichar\Infrastructure\Repository\FicharRepository")
 */
class Fichar implements FicharInterface
{
    use IdentifiableTrait;
    use FicharValidation;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    public $date;

    /**
     * @ORM\Column(type="integer", options={"default" : 1})
     *
     * @var int
     */
    public $action = self::IN;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    public $latitude;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    public $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\Grh\Employee\Domain\Employee")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     *
     * @var Employee
     */
    private $employee = null;

    /**
     * @return Employee|null
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @param $employee
     *
     * @return bool
     */
    public function setEmployee($employee): bool
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
    public function getDate(): \DateTime
    {
        return (empty($this->date))? new \DateTime() : $this->date;
    }

    /**
     * @return bool
     */
    public function setAction($inOut): bool
    {
        if ($inOut == self::IN || $inOut == self::OUT) {
            $this->action = $inOut;

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     */
    public function setLatitude($latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return string
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     */
    public function setLongitude($longitude): void
    {
        $this->longitude = $longitude;
    }
}