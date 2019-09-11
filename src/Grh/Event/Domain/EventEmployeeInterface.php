<?php
namespace CTIC\Grh\Event\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Domain\IdentifiableInterface;
use CTIC\Grh\Employee\Domain\Employee;

interface EventEmployeeInterface extends IdentifiableInterface, EntityInterface
{
    const TURN_CONCERT = 0;
    const TURN_DISCOTHEQUE = 1;
    const TURN_ALL = 2;

    /**
     * @return Employee|null
     */
    public function getEmployee();

    /**
     * @return Event|null
     */
    public function getEvent();

    /**
     * @return int
     */
    public function getTurn();
}