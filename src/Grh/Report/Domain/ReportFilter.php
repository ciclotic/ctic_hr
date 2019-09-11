<?php
namespace CTIC\Grh\Report\Domain;

use CTIC\App\Base\Domain\EntityInterface;

class ReportFilter implements EntityInterface
{

    /**
     * @var bool
     */
    public $send = false;

    /**
     * @var bool
     */
    public $sendFichar = false;

    /**
     * @var \DateTime
     */
    public $fromDate;

    /**
     * @var \DateTime
     */
    public $toDate;

    /**
     * EmployeeGrowth constructor.
     */
    public function __construct()
    {
        $this->fromDate = new \DateTime();
        $this->toDate = new \DateTime();
    }
}