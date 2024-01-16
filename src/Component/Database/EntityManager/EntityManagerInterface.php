<?php

namespace Kaa\Component\Database\EntityManager;

use Kaa\Component\Database\EntityInterface;

interface EntityManagerInterface
{
    public function __construct(
        string $host,
        string $database,
        string $user,
        string $password
    );

    /**
     * @template T of object
     * @kphp-generic T
     *
     * @param class-string<T> $entityClass
     * @return T|null
     */
    public function find(string $entityClass, int $id): ?object;

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
}
