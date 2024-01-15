<?php

namespace Kaa\Component\Database\EntityManager;

interface EntityManagerInterface
{
    public function __construct(
        string $host,
        string $database,
        string $user,
        string $password
    );

    /**
     * @template T
     * @kphp-generic T
     *
     * @param class-string<T> $entityClass
     * @return T|null
     */
    public function find(string $entityClass, int $id): ?object;

    /**
     * @template T
     * @kphp-generic T
     *
     * @param class-string<T> $entityClass
     * @return T
     */
    public function new(string $entityClass): object;

    public function flush(): void;
}
