<?php

namespace Kaa\Component\Database\EntityManager;

use Kaa\Component\Database\Dto\EntityWithValueSet;
use Kaa\Component\Database\EntityInterface;
use PDO;

interface EntityManagerInterface
{
    public function __construct(
        string $host,
        string $database,
        string $user,
        string $password
    );

    /**
     * @template T of EntityInterface
     * @kphp-generic T
     *
     * @param class-string<T> $entityClass
     * @return T|null
     */
    public function find(string $entityClass, int $id): ?EntityInterface;

    /**
     * @template T of EntityInterface
     * @kphp-generic T
     *
     * @param array<string, string|int> $criteria
     * @param class-string<T> $entityClass
     * @return T|null
     */
    public function findOneBy(string $entityClass, array $criteria): ?EntityInterface;

    /**
     * @template T of EntityInterface
     * @kphp-generic T
     *
     * @param array<string, string|int> $criteria
     * @param array<string, string> $order
     * @param class-string<T> $entityClass
     * @return T[]
     */
    public function findBy(
        string $entityClass,
        array $criteria,
        array $order = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    public function refresh(EntityInterface $entity): void;

    /**
     * @template T of object
     * @kphp-generic T
     *
     * @param class-string<T> $entityClass
     * @return T
     */
    public function new(string $entityClass): object;

    public function flush(): void;

    public function remove(EntityInterface $entity): void;

    public function _getPdo(): PDO;

    /**
     * @template T of \Kaa\Component\Database\EntityInterface
     * @kphp-generic T
     *
     * @param class-string<T> $entityClass
     */
    public function createQueryBuilder(string $entityClass, string $alias): \Kaa\Component\Database\QueryBuilder\AbstractQueryBuilder;

    /**
     * @return array<string, \Kaa\Component\Database\Dto\EntityWithValueSet>
     */
    public function _getManagedEntities(): array;

    public function _addManagedEntity(string $key, EntityWithValueSet $object): self;
}
