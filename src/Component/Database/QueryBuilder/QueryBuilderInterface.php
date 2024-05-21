<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder;

use Kaa\Component\Database\EntityInterface;
use Kaa\Component\Database\QueryBuilder\Query\Expr;

interface QueryBuilderInterface
{
    public function select(string $select): self;

    public function join(string $join, string $alias): self;

    public function leftJoin(string $join, string $alias): self;

    public function fetchJoin(string $join, string $alias): self;

    public function leftFetchJoin(string $join, string $alias): self;

    public function where(Expr $expr): self;

    /**
     * @param string[] $columns
     */
    public function groupBy(array $columns): self;

    public function having(Expr $expr): self;

    public function setMaxResult(int $limit): self;

    public function setFirstResult(int $offset): self;

    public function setParameter(string $key, string $value): self;

    public function addOrderBy(string $sort, string $order = Expr\OrderBy::DESC): self;

    public function getSql(): string;

    /**
     * @template T of \Kaa\Component\Database\EntityInterface
     * @kphp-generic T
     * @param class-string<T> $entityClass
     * @return T[]
     */
    public function getResult(string $entityClass): array;

    /**
     * @template T of \Kaa\Component\Database\EntityInterface
     * @kphp-generic T
     * @param class-string<T> $entityClass
     * @return T|null
     */
    public function getOneOrNullResult(string $entityClass): EntityInterface|null;

    /**
     * @template T of object
     * @kphp-generic T
     * @param callable(mixed):T[] $hydrate
     * @return T[]
     */
    public function getCustomResult(callable $hydrate): array;
}
