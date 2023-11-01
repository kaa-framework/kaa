# Event Dispatcher

### Содержание

* [Введение](#введение)
* [EventDispatcherInterface](#eventdispatcherinterface)
* [Event](#event)
* [Пример использования](#пример-использования)

### Введение

EventDispatcher позволяет класса обмениваться синхронными сообщениями друг с другом.

### EventDispatcherInterface

```php
<?php

interface EventDispatcherInterface
{
    /**
     * Добавляет слушателя для определённого сообщения
     *
     * @param callable(EventInterface): void $eventListener
     * @param int $priority Чем выше это значение, тем раньше будут вызван этот слушатель
     */
    public function addListener(string $eventName, callable $eventListener, int $priority = 0): self;

    /**
     * @param callable(EventInterface): void $eventListener
     * Удалить подписку переданного слушателя на переданное сообщение
     */
    public function removeListener(string $eventName, callable $eventListener): void;
    
    /**
     * Отправляет $event: вызывает всех слушателей, которые подписаны на это сообщение
     * до тех пор, пока не будут вызваны все слушатели, либо пока у сообщения не будет вызван метод ->stopPropagation()
     */
    public function dispatch(EventInterface $event, string $eventName): self;
}
```

### Event

```php

interface EventInterface
{
    public function stopPropagation(): void;

    public function isPropagationStopped(): bool;
}

class AbstractEvent implements EventInterface
{
    private bool $isPropagationStopped = false;

    public function stopPropagation(): void
    {
        $this->isPropagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
```

### Пример использования

```php
<?php

$dispatcher = new EventDipatcher();

function eventListener(EventInterface $event): void
{
    echo 'Event Received';
}

$dispatcher->addListener('my_event', 'eventListener');
$dispatcher->dispatch(new AbstractEvent(), 'my_event');
```
