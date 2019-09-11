<?php
namespace CTIC\Grh\Employee\Domain;

use CTIC\App\Base\Domain\EntityInterface;

class EmployeeGrowth implements EntityInterface
{

    /**
     * @var Employee
     */
    public $employee = null;

    /**
     * @var \DateTime
     */
    public $fromDate;

    /**
     * @var \DateTime
     */
    public $contractualDate;

    /**
     * @var int
     */
    public $hours = 0;

    /**
     * EmployeeGrowth constructor.
     */
    public function __construct()
    {
        $this->fromDate = new \DateTime();
        $this->contractualDate = new \DateTime();
    }
}