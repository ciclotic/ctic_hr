<?php
namespace CTIC\Grh\Event\Domain;

use CTIC\Grh\Employee\Domain\Employee;
use Doctrine\ORM\Mapping as ORM;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Event\Domain\Validation\EventEmployeeValidation;

/**
 * @ORM\Entity(repositoryClass="CTIC\Grh\Event\Infrastructure\Repository\EventEmployeeRepository")
 */
class EventEmployee implements EventEmployeeInterface
{
    use IdentifiableTrait;
    use EventEmployeeValidation;

    /**
     * @ORM\ManyToOne(targetEntity="CTIC\Grh\Employee\Domain\Employee", fetch = "EAGER")
     *
     * @var Employee
     */
    private $employee;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="eventEmployee", fetch = "EAGER")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *
     * @var Event
     */
    private $event;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    public $turn = 0;

    /**
     * @return Employee|null
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
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
     * @return Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function setEvent(Event $event): bool
    {
        if (get_class($event) != Event::class) {
            return false;
        }

        $this->event = $event;

        return true;
    }

    /**
     * @return int
     */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * @param $turn
     * @return bool
     */
    public function setTurn($turn): bool
    {
        if ($turn == self::TURN_ALL || $turn == self::TURN_CONCERT || $turn == self::TURN_DISCOTHEQUE)
        {
            $this->turn = $turn;

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }
}