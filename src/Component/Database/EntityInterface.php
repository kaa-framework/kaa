<?php

namespace Kaa\Component\Database;

use Kaa\Component\Database\Dto\EntityWithValueSet;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;

interface EntityInterface
{
    /**
     * @internal
     *
     * @return string[]
     */
    public function _getColumnNames(): array;

    /**
     * @internal
     *
     * @param array<string, EntityWithValueSet> $managedEntities
     * @return EntityInterface[] Сущности, которые должны стать managed
     */
    public function _hydrate(mixed $values, EntityManagerInterface $entityManager, array $managedEntities): array;

    /**
     * @internal
     *
     * @param array<string, int[]> $ids
     * @param array<string, EntityWithValueSet> $managedEntities
     * @return EntityInterface[] Сущности, которые должны стать managed
     */
    public function _hydrateOneToMany(array $ids, EntityManagerInterface $entityManager, array $managedEntities): array;

    /**
     * @internal
     */
    public function _getValues(): mixed;

    /**
     * @internal
     */
    public function _getId(): ?int;

    /**
     * @internal
     */
    public function _setId(int $id): void;

    /**
     * @internal
     */
    public function _getIdColumnName(): string;

    /**
     * @internal
     */
    public function _getTableName(): string;

    /**
     * @internal
     */
    public function _isInitialized(): bool;

    /**
     * @internal
     */
    public function _setInitialized(): void;

    /**
     * @internal
     */
    public function _getOid(): string;

    /**
     * @internal
     *
     * @return string[]
     */
    public function _getNotInsertedOids(): array;

    /**
     * @internal
     */
    public function _getEntityClassName(): string;

    /**
     * @internal
     */
    public function _isQueuedToRemove(): bool;

    /**
     * @internal
     */
    public function _setIsQueuedToRemove(bool $isQueuedToRemove = true): void;
}
