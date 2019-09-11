<?php
namespace CTIC\Grh\Employee\Domain;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Employee\Domain\Validation\EmployeeAttachedValidation;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="CTIC\Grh\Employee\Infrastructure\Repository\EmployeeAttachedRepository")
 */
class EmployeeAttached implements EmployeeAttachedInterface
{
    use IdentifiableTrait;
    use EmployeeAttachedValidation;

    /**
     * @var string|File
     *
     * @ORM\Column(type="string")
     *
     */
    public $attached = '';

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(targetEntity="CTIC\Grh\Employee\Domain\Employee", inversedBy="attached")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     *
     */
    public $employee;

    /**
     * @return string|array|File
     */
    public function getAttached()
    {
        if (is_string($this->attached) &&
            !empty($this->attached) &&
            file_exists(getcwd() . self::ATTACHED_PATH . '/' . $this->attached)
        ) {
            return new File(getcwd() . self::ATTACHED_PATH . '/' . $this->attached);
        }

        if (is_array($this->attached)) {
            return $this->attached;
        }

        return new File(getcwd() . self::ATTACHED_PATH . '/control.pdf');
    }

    /**
     * @param string $attached
     *
     * @return void
     */
    public function setAttached($attached)
    {
        $this->attached = $attached;
    }

    /**
     * @return Employee|null
     */
    public function getEmployee()
    {
        return $this->employee;
    }
}