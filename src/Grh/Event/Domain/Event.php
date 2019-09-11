<?php
namespace CTIC\Grh\Event\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Event\Domain\Validation\EventValidation;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="CTIC\Grh\Event\Infrastructure\Repository\EventRepository")
 */
class Event implements EventInterface
{
    use IdentifiableTrait;
    use EventValidation;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    public $description;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $iCalUID;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    public $creationDate;

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
    public $shiftChangeDate;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    public $toDate;

    /**
     * @ORM\OneToMany(targetEntity="EventEmployee", mappedBy="event", cascade={"all"})
     *
     * @var EventEmployee
     */
    private $eventEmployee;

    /**
     * Employee constructor.
     */
    public function __construct()
    {
        $this->eventEmployee = new ArrayCollection();
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
    public function getICalUID(): string
    {
        return (empty($this->iCalUID))? '' : $this->iCalUID;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (empty($this->description))? '' : $this->description;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate(): \DateTime
    {
        return (empty($this->creationDate))? new \DateTime() : $this->creationDate;
    }

    /**
     * @return bool
     */
    public function isMoreThan8Hours(): bool
    {
        $intervalDates = $this->getFromDate()->diff($this->getToDate());
        $eventHours = (float) (($intervalDates->d * 24) + $intervalDates->h + ($intervalDates->i / 60));
        return ($eventHours > 8)? true : false;
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
    public function getShiftChangeDate(): \DateTime
    {
        return (empty($this->shiftChangeDate))? new \DateTime() : $this->shiftChangeDate;
    }

    /**
     * @return \DateTime
     */
    public function getToDate(): \DateTime
    {
        return (empty($this->toDate))? new \DateTime() : $this->toDate;
    }

    /**
     * @return EventEmployee[]|ArrayCollection
     */
    public function getEventEmployee()
    {
        return $this->eventEmployee;
    }

    /**
     * @param $eventEmployee
     * @return bool
     */
    public function setEventEmployee($eventEmployee): bool
    {
        if (get_class($eventEmployee) != ArrayCollection::class) {
            return false;
        }

        $this->eventEmployee = $eventEmployee;

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