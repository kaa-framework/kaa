<?php

namespace Kaa\Test\Component\EventDispatcher;

use Kaa\Component\EventDispatcher\AbstractEvent;
use Kaa\Component\EventDispatcher\EventDispatcher;
use Kaa\Component\EventDispatcher\EventInterface;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    private const FIRST_EVENT = 'first.event';
    private const SECOND_EVENT = 'second.event';

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testInitialStateHasNoListeners(): void
    {
        $this->assertEquals([], $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertEquals([], $this->dispatcher->getListeners(self::SECOND_EVENT));
        $this->assertFalse($this->dispatcher->hasListeners());
        $this->assertFalse($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertFalse($this->dispatcher->hasListeners(self::SECOND_EVENT));
    }

    public function testAddListener(): void
    {
        $listener = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener, 'handle'])
            ->addListener(self::SECOND_EVENT, [$listener, 'handle']);

        $this->assertTrue($this->dispatcher->hasListeners());
        $this->assertTrue($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertTrue($this->dispatcher->hasListeners(self::SECOND_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::SECOND_EVENT));
    }

    private function createEventListener(string $dataString = '', bool $shallStopPropagation = false): TestEventListener
    {
        return new TestEventListener($dataString, $shallStopPropagation);
    }

    public function testGetListenersSortsByPriority(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();
        $listener3 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], -10)
            ->addListener(self::FIRST_EVENT, [$listener2, 'handle'], 10)
            ->addListener(self::FIRST_EVENT, [$listener3, 'handle']);

        $expected = [
            [$listener2, 'handle'],
            [$listener3, 'handle'],
            [$listener1, 'handle'],
        ];

        $this->assertSame($expected, $this->dispatcher->getListeners(self::FIRST_EVENT));
    }

    public function testGetListenerPriority(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], -10)
            ->addListener(self::FIRST_EVENT, [$listener2, 'handle']);

        $this->assertSame(-10, $this->dispatcher->getListenerPriority(self::FIRST_EVENT, [$listener1, 'handle']));
        $this->assertSame(0, $this->dispatcher->getListenerPriority(self::FIRST_EVENT, [$listener2, 'handle']));
        $this->assertNull($this->dispatcher->getListenerPriority(self::SECOND_EVENT, [$listener2, 'handle']));
        $this->assertNull($this->dispatcher->getListenerPriority(self::FIRST_EVENT, [$this->createEventListener(), 'handle']));
    }

    public function testDispatch(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();
        $listener3 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener1, 'handle'])
            ->addListener(self::FIRST_EVENT, [$listener2, 'handle'])
            ->addListener(self::SECOND_EVENT, [$listener3, 'handle']);

        $this->dispatcher
            ->dispatch(new TestEvent(), self::FIRST_EVENT)
            ->dispatch(new TestEvent(), self::SECOND_EVENT);

        $this->assertTrue($listener1->wasInvoked);
        $this->assertTrue($listener2->wasInvoked);
        $this->assertTrue($listener3->wasInvoked);
    }

    public function testDispatchByPriority(): void
    {
        $listener1 = $this->createEventListener('1');
        $listener2 = $this->createEventListener('2');
        $listener3 = $this->createEventListener('3');

        $testEvent = new TestEvent();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], -10)
            ->addListener(self::FIRST_EVENT, [$listener2, 'handle'], 10)
            ->addListener(self::FIRST_EVENT, [$listener3, 'handle']);

        $this->dispatcher->dispatch($testEvent, self::FIRST_EVENT);

        $this->assertEquals('231', $testEvent->dataString);
    }

    public function testStopPropagation(): void
    {
        $listener1 = $this->createEventListener(shallStopPropagation: true);
        $listener2 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], 10)
            ->addListener(self::FIRST_EVENT, [$listener2, 'handle']);

        $this->dispatcher->dispatch(new TestEvent(), self::FIRST_EVENT);

        $this->assertTrue($listener1->wasInvoked);
        $this->assertFalse($listener2->wasInvoked);
    }

    public function testRemoveListeners(): void
    {
        $listener1 = $this->createEventListener();
        $listener2 = $this->createEventListener();
        $listener3 = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener1, 'handle'])
            ->addListener(self::FIRST_EVENT, [$listener2, 'handle'])
            ->addListener(self::SECOND_EVENT, [$listener3, 'handle']);

        $this->assertCount(2, $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::SECOND_EVENT));

        $this->dispatcher->removeListener(self::FIRST_EVENT, [$listener1, 'handle']);

        $this->assertCount(1, $this->dispatcher->getListeners(self::FIRST_EVENT));
        $this->assertCount(1, $this->dispatcher->getListeners(self::SECOND_EVENT));

        $this->dispatcher->dispatch(new TestEvent(), self::FIRST_EVENT);

        $this->assertFalse($listener1->wasInvoked);
    }

    public function testRemoveListenerOnlyForOneEvent(): void
    {
        $listener = $this->createEventListener();

        $this->dispatcher
            ->addListener(self::FIRST_EVENT, [$listener, 'handle'])
            ->addListener(self::SECOND_EVENT, [$listener, 'handle']);

        $this->assertTrue($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertTrue($this->dispatcher->hasListeners(self::SECOND_EVENT));

        $this->dispatcher->removeListener(self::FIRST_EVENT, [$listener, 'handle']);

        $this->assertFalse($this->dispatcher->hasListeners(self::FIRST_EVENT));
        $this->assertTrue($this->dispatcher->hasListeners(self::SECOND_EVENT));

        $this->dispatcher->dispatch(new TestEvent(), self::SECOND_EVENT);

        $this->assertTrue($listener->wasInvoked);
    }
}

class TestEvent extends AbstractEvent
{
    public string $dataString = '';
}

class TestEventListener
{
    public bool $wasInvoked = false;

    public function __construct(
        private readonly string $dataString = '',
        private readonly bool $shallStopPropagation = false,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        $testEvent = instance_cast($event, TestEvent::class);
        if ($testEvent === null) {
            return;
        }

        $testEvent->dataString .= $this->dataString;
        $this->wasInvoked = true;

        if ($this->shallStopPropagation) {
            $event->stopPropagation();
        }
    }
}
