<?php

declare(strict_types=1);

namespace Kaa\Component\EventDispatcher;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var shape(listener: (callable(EventInterface): void), priority: int)[][] */
    private array $listeners = [];

    /** @var (callable(EventInterface): void)[][] */
    private array $sortedListeners = [];

    public function dispatch(EventInterface $event, string $eventName): self
    {
        if (!array_key_exists($eventName, $this->listeners) || $this->listeners[$eventName] === []) {
            return $this;
        }

        if (!array_key_exists($eventName, $this->sortedListeners) || $this->sortedListeners[$eventName] === []) {
            $this->sortListeners($eventName);
        }

        $this->executeListeners($event, $eventName);

        return $this;
    }

    private function sortListeners(string $eventName): void
    {
        usort(
            $this->listeners[$eventName],
            static fn ($first, $second) => -($first['priority'] <=> $second['priority']),
        );

        $this->sortedListeners[$eventName] = array_map(
            static fn ($listenerShape) => $listenerShape['listener'],
            $this->listeners[$eventName],
        );
    }

    private function executeListeners(EventInterface $event, string $eventName): void
    {
        foreach ($this->sortedListeners[$eventName] as $listener) {
            if ($event->isPropagationStopped()) {
                return;
            }

            $listener($event);
        }
    }

    /**
     * @param callable(EventInterface): void $eventListener
     */
    public function addListener(string $eventName, callable $eventListener, int $priority = 0): self
    {
        $this->listeners[$eventName][] = shape([
            'listener' => $eventListener,
            'priority' => $priority,
        ]);
        unset($this->sortedListeners[$eventName]);

        return $this;
    }

    /**
     * @param callable(EventInterface): void $eventListener
     */
    public function removeListener(string $eventName, callable $eventListener): void
    {
        foreach ($this->listeners[$eventName] as $index => $listener) {
            if ($listener['listener'] === $eventListener) {
                unset($this->listeners[$eventName][$index], $this->sortedListeners[$eventName]);

                return;
            }
        }
    }

    public function hasListeners(?string $eventName = null): bool
    {
        if ($eventName !== null) {
            return array_key_exists($eventName, $this->listeners) && $this->listeners[$eventName] !== [];
        }

        foreach ($this->listeners as $eventListeners) {
            if ($eventListeners !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return (callable(EventInterface): void)[]
     */
    public function getListeners(string $eventName): array
    {
        if (!array_key_exists($eventName, $this->listeners) || $this->listeners[$eventName] === []) {
            return [];
        }

        if (!array_key_exists($eventName, $this->sortedListeners) || $this->sortedListeners[$eventName] === []) {
            $this->sortListeners($eventName);
        }

        return $this->sortedListeners[$eventName];
    }

    /**
     * @param callable(EventInterface): void $listener
     */
    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        if (!array_key_exists($eventName, $this->listeners) || $this->listeners[$eventName] === []) {
            return null;
        }

        foreach ($this->listeners[$eventName] as $listenerShape) {
            if ($listenerShape['listener'] === $listener) {
                return $listenerShape['priority'];
            }
        }

        return null;
    }
}
