<?php

declare(strict_types=1);

namespace Kaa\Component\EventDispatcher\Test;

use Kaa\Component\EventDispatcher\AbstractEvent;
use Kaa\Component\EventDispatcher\EventDispatcher;
use Kaa\Component\EventDispatcher\EventInterface;

trait Local
{
    protected const FIRST_EVENT = 'first.event';
    protected const SECOND_EVENT = 'second.event';

    private function createEventListener(string $dataString = '', bool $shallStopPropagation = false): TestEventListener
    {
        return new TestEventListener($dataString, $shallStopPropagation);
    }
}

uses(Local::class);

beforeEach(function () {
    $this->dispatcher = new EventDispatcher();
});

test('initial state does not have listeners', function () {
    expect($this->dispatcher)
        ->getListeners(self::FIRST_EVENT)->toBeEmpty()
        ->getListeners(self::SECOND_EVENT)->toBeEmpty()
        ->hasListeners()->toBeFalse()
        ->hasListeners(self::FIRST_EVENT)->toBeFalse()
        ->hasListeners(self::SECOND_EVENT)->toBeFalse();
});

it('adds listeners', function () {
    $listener = $this->createEventListener();

    $this->dispatcher
        ->addListener(self::FIRST_EVENT, [$listener, 'handle'])
        ->addListener(self::SECOND_EVENT, [$listener, 'handle']);

    expect($this->dispatcher)
        ->hasListeners()->toBeTrue()
        ->hasListeners(self::FIRST_EVENT)->toBeTrue()
        ->hasListeners(self::SECOND_EVENT)->toBeTrue()
        ->getListeners(self::FIRST_EVENT)->toHaveCount(1)
        ->getListeners(self::SECOND_EVENT)->toHaveCount(1);
});

it('correctly sorts listeners by priority', function () {
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

    expect($this->dispatcher)
        ->getListeners(self::FIRST_EVENT)->toBe($expected);
});

it('correctly gets listener`s priority', function () {
    $listener1 = $this->createEventListener();
    $listener2 = $this->createEventListener();

    $this->dispatcher
        ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], -10)
        ->addListener(self::FIRST_EVENT, [$listener2, 'handle']);

    expect($this->dispatcher)
        ->getListenerPriority(self::FIRST_EVENT, [$listener1, 'handle'])->toBe(-10)
        ->getListenerPriority(self::FIRST_EVENT, [$listener2, 'handle'])->toBe(0)
        ->getListenerPriority(self::SECOND_EVENT, [$listener2, 'handle'])->toBeNull()
        ->getListenerPriority(self::FIRST_EVENT, [$this->createEventListener(), 'handle'])->toBeNull();
});

it('dispatches correctly', function () {
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

    expect($listener1->wasInvoked)->toBeTrue()
        ->and($listener2->wasInvoked)->toBeTrue()
        ->and($listener3->wasInvoked)->toBeTrue();
});

it('dispatches with correct priority', function () {
    $listener1 = $this->createEventListener('1');
    $listener2 = $this->createEventListener('2');
    $listener3 = $this->createEventListener('3');

    $testEvent = new TestEvent();

    $this->dispatcher
        ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], -10)
        ->addListener(self::FIRST_EVENT, [$listener2, 'handle'], 10)
        ->addListener(self::FIRST_EVENT, [$listener3, 'handle']);

    $this->dispatcher->dispatch($testEvent, self::FIRST_EVENT);

    expect($testEvent->dataString)
        ->toBe('231');
});

it('stops propagation', function () {
    $listener1 = $this->createEventListener(shallStopPropagation: true);
    $listener2 = $this->createEventListener();

    $this->dispatcher
        ->addListener(self::FIRST_EVENT, [$listener1, 'handle'], 10)
        ->addListener(self::FIRST_EVENT, [$listener2, 'handle']);

    $this->dispatcher->dispatch(new TestEvent(), self::FIRST_EVENT);

    expect($listener1->wasInvoked)->toBeTrue()
        ->and($listener2->wasInvoked)->toBeFalse();
});

it('removes listeners', function () {
    $listener1 = $this->createEventListener();
    $listener2 = $this->createEventListener();
    $listener3 = $this->createEventListener();

    $this->dispatcher
        ->addListener(self::FIRST_EVENT, [$listener1, 'handle'])
        ->addListener(self::FIRST_EVENT, [$listener2, 'handle'])
        ->addListener(self::SECOND_EVENT, [$listener3, 'handle']);

    expect($this->dispatcher)
        ->getListeners(self::FIRST_EVENT)->toHaveCount(2)
        ->getListeners(self::SECOND_EVENT)->toHaveCount(1);

    $this->dispatcher->removeListener(self::FIRST_EVENT, [$listener1, 'handle']);

    expect($this->dispatcher)
        ->getListeners(self::FIRST_EVENT)->toHaveCount(1)
        ->getListeners(self::SECOND_EVENT)->toHaveCount(1);

    $this->dispatcher->dispatch(new TestEvent(), self::FIRST_EVENT);

    expect($listener1->wasInvoked)->toBeFalse();
});

it('correctly removes listener for only one event', function () {
    $listener = $this->createEventListener();

    $this->dispatcher
        ->addListener(self::FIRST_EVENT, [$listener, 'handle'])
        ->addListener(self::SECOND_EVENT, [$listener, 'handle']);

    expect($this->dispatcher)
        ->hasListeners(self::FIRST_EVENT)->toBeTrue()
        ->hasListeners(self::SECOND_EVENT)->toBeTrue();

    $this->dispatcher->removeListener(self::FIRST_EVENT, [$listener, 'handle']);

    expect($this->dispatcher)
        ->hasListeners(self::FIRST_EVENT)->toBeFalse()
        ->hasListeners(self::SECOND_EVENT)->toBeTrue();

    $this->dispatcher->dispatch(new TestEvent(), self::SECOND_EVENT);

    expect($listener->wasInvoked)
        ->toBeTrue();
});

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
