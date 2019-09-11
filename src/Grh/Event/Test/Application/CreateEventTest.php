<?php
declare(strict_types=1);

namespace CTIC\Grh\Event\Test\Application;

use CTIC\Grh\Event\Application\CreateEvent;
use CTIC\Grh\Event\Domain\Command\EventCommand;
use CTIC\Grh\Event\Domain\Event;
use PHPUnit\Framework\TestCase;

final class CreateEventTest extends TestCase
{
    public function testCreateAssert()
    {
        $EventCommandRyu = new EventCommand();
        $EventCommandRyu->name = 'ryu';
        $EventRyu = CreateEvent::create($EventCommandRyu);

        $this->assertEquals(Event::class, get_class($EventRyu));
    }
}