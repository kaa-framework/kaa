<?php

declare(strict_types=1);

namespace Kaa\Component\EventDispatcher;

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

    /**
     * Возвращает, есть ли слушатели, если $eventName === null
     * или есть ли слушатели переданного события, если $eventName !== null
     */
    public function hasListeners(?string $eventName = null): bool;

    /**
     * Возвращает список слушателей, подписанных на переданное событие,
     * отсортированных в порядке, в котором они будут вызваны
     * @return (callable(EventInterface): void)[]
     */
    public function getListeners(string $eventName): array;

    /**
     * @param callable(EventInterface): void $listener
     * Возвращает приоритет слушателя или null, если слушать не подписан на событие
     */
    public function getListenerPriority(string $eventName, callable $listener): ?int;
}
