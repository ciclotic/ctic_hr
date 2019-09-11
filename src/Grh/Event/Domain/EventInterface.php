<?php
namespace CTIC\Grh\Event\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Domain\IdentifiableInterface;
use Doctrine\Common\Collections\ArrayCollection;

interface EventInterface extends IdentifiableInterface, EntityInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function getICalUID(): string;

    public function isMoreThan8Hours(): bool;
    public function getCreationDate(): \DateTime;
    public function getFromDate(): \DateTime;
    public function getShiftChangeDate(): \DateTime;
    public function getToDate(): \DateTime;

    /**
     * @return EventEmployee[]|ArrayCollection
     */
    public function getEventEmployee();
}