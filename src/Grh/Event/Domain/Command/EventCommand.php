<?php
namespace CTIC\Grh\Event\Domain\Command;

use CTIC\App\Base\Domain\Command\CommandInterface;
use CTIC\Grh\Event\Domain\EventEmployee;
use Doctrine\Common\Collections\ArrayCollection;

class EventCommand implements CommandInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;
}