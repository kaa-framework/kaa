<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder;

use Kaa\Component\Database\EntityInterface;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;
use Kaa\Component\Database\QueryBuilder\Exception\QueryBuilderException;
use Kaa\Component\Database\QueryBuilder\Query\Dto\EntityInfo;
use Kaa\Component\Database\QueryBuilder\Query\Expr;
use Kaa\Component\Database\QueryBuilder\Query\Expr\Join;
use Kaa\Component\Database\QueryBuilder\Query\Expr\OrderBy;
use Kaa\Component\Database\QueryBuilder\Query\QueryParts;

abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    protected QueryParts $queryParts;

    /** @var string[] */
    protected array $params = [];

    protected EntityManagerInterface $entityManager;

    protected EntityInfo $entityInfo;

    public function __construct(EntityManagerInterface $entityManager, string $className, string $alias)
    {
        $this->entityManager = $entityManager;
        $classInfo = $this->getEntityInfo($className);
        $this->entityInfo = $classInfo;
        $this->queryParts = new QueryParts($classInfo->tableName, $alias);
    }

    public function select(string $select): self
    {
        $this->queryParts->setSelect($select);

        return $this;
    }

    public function join(string $join, string $alias): self
    {
        $this->addJoin($join, $alias, Join::INNER_JOIN);

        return $this;
    }

    private function addJoin(string $join, string $alias, string $type): void
    {
        $tablesInfo = [$this->queryParts->getFrom()->getAlias() => $this->entityInfo];
        foreach ($this->queryParts->getJoin() as $existsJoin) {
            $tablesInfo = array_merge($tablesInfo, [$existsJoin->getReferenceClassAlias() => $existsJoin->getReferenceClassInfo()]);
        }

        if (array_key_exists($alias, $tablesInfo)) {
            throw new QueryBuilderException("Alias {$alias} already defined!");
        }

        $currentAlias = substr($join, 0, (int) strpos($join, '.'));

        if (!array_key_exists($currentAlias, $tablesInfo)) {
            throw new QueryBuilderException("Alias {$currentAlias} is not exists!");
        }

        $join = substr($join, (int) strpos($join, '.') + 1);
        if (!array_key_exists($join, $tablesInfo[$currentAlias]->referenceColumns)) {
            throw new QueryBuilderException("Incorrect fetch join using. No {$join} in {$this->entityInfo->entityName}");
        }

        $joinColumn = $tablesInfo[$currentAlias]->referenceColumns[$join];
        $referenceClassInfo = $this->getEntityInfo/* <EntityInterface> */ ($joinColumn);
        $this->queryParts->join($join, $currentAlias, $alias, $tablesInfo[$currentAlias], $referenceClassInfo, $type);
    }

    public function leftJoin(string $join, string $alias): self
    {
        $this->addJoin($join, $alias, Join::LEFT_JOIN);

        return $this;
    }

    public function fetchJoin(string $join, string $alias): self
    {
        $this->addJoin($join, $alias, Join::FETCH_JOIN);

        return $this;
    }

    public function leftFetchJoin(string $join, string $alias): self
    {
        $this->addJoin($join, $alias, Join::LEFT_FETCH_JOIN);

        return $this;
    }

    public function where(Expr $expr): self
    {
        $this->queryParts->setExpression($expr);

        return $this;
    }

    public function setParameter(string $key, mixed $value): self
    {
        if (is_array($value)) {
            $value = array_map(static fn ($var) => is_string($var) ? "'{$var}'" : $var, $value);
            $value = implode(', ', $value);
        } elseif (is_string($value)) {
            $value = "'{$value}'";
        }

        $this->params[$key] = (string) $value;

        return $this;
    }

    /**
     * @param string[] $columns
     */
    public function groupBy(array $columns): self
    {
        $this->queryParts->setGroupBy($columns);

        return $this;
    }

    public function having(Expr $expr): self
    {
        $this->queryParts->setHaving($expr);

        return $this;
    }

    public function setFirstResult(int $offset): self
    {
        $this->queryParts->setFirstResult($offset);

        return $this;
    }

    public function setMaxResult(int $limit): self
    {
        $this->queryParts->setMaxResult($limit);

        return $this;
    }

    public function addOrderBy(string $sort, string $order = OrderBy::DESC): self
    {
        $this->queryParts->addOrderBy($sort, $order);

        return $this;
    }

    protected function getSelectPart(): string
    {
        $sql = 'SELECT ';
        $columns = $this->getEntityColumns($this->entityInfo->entityName);
        $columnsPrefixed = array_map(fn ($var) => $this->queryParts->getFrom()->getAlias() . ".{$var}", $columns);
        $columnsPrefixed = array_map(static fn ($var) => $var . " AS \"{$var}\"", $columnsPrefixed);
        foreach ($this->queryParts->getJoin() as $join) {
            if ($join->getType() === Join::LEFT_FETCH_JOIN || $join->getType() === Join::FETCH_JOIN) {
                $mas = $this->getEntityColumns($join->getReferenceClassInfo()->entityName);
                $diffPrefixed = array_map(static fn ($var) => $join->getReferenceClassAlias() . ".{$var}", $mas);
                $diffPrefixed = array_map(static fn ($var) => $var . " AS \"{$var}\"", $diffPrefixed);
                $columnsPrefixed = array_merge($columnsPrefixed, $diffPrefixed);
            }
        }

        $sql .= implode(', ', $columnsPrefixed) . ' ';
        $sql .= $this->queryParts->getFrom()->getQueryPart();
        if (count($this->queryParts->getJoin()) > 0) {
            foreach ($this->queryParts->getJoin() as $join) {
                $sql .= $join->getQueryPart();
            }
        }

        return $sql;
    }

    public function getSql(): string
    {
        $sql = ' SELECT ';
        if ($this->queryParts->getSelect() === null) {
            $fetchCount = 0;
            foreach ($this->queryParts->getJoin() as $join) {
                if (($join->getType() === 'FETCH') || ($join->getType() === 'LEFT_FETCH')) {
                    $fetchCount++;
                }
            }

            if ($fetchCount === 0) {
                $columns = $this->getEntityColumns($this->entityInfo->entityName);
                $columnsPrefixed = array_map(fn ($var) => $this->queryParts->getFrom()->getAlias() . ".{$var}", $columns);
                $columnsPrefixed = array_map(static fn ($var) => $var . " AS \"{$var}\"", $columnsPrefixed);
                foreach ($this->queryParts->getJoin() as $join) {
                    if ($join->getType() === Join::LEFT_FETCH_JOIN || $join->getType() === Join::FETCH_JOIN) {
                        $mas = $this->getEntityColumns($join->getReferenceClassInfo()->entityName);
                        $diffPrefixed = array_map(static fn ($var) => $join->getReferenceClassAlias() . ".{$var}", $mas);
                        $diffPrefixed = array_map(static fn ($var) => $var . " AS \"{$var}\"", $diffPrefixed);
                        $columnsPrefixed = array_merge($columnsPrefixed, $diffPrefixed);
                    }
                }
            } else {
                $sql .= ' DISTINCT ';
                $columns = $this->getEntityColumns($this->entityInfo->entityName)[0];
                $columnsPrefixed = array_map(fn ($var) => $this->queryParts->getFrom()->getAlias() . ".{$var}", [$columns]);
                $columnsPrefixed = array_map(static fn ($var) => $var . " AS \"{$var}\"", $columnsPrefixed);
                foreach ($this->queryParts->getOrderBy()->parts as $key => $value) {
                    $column = $key . " AS \"{$key}\"";
                    if (!in_array($column, $columnsPrefixed, true)) {
                        $columnsPrefixed[] = $column;
                    }
                }
            }

            $sql .= implode(', ', $columnsPrefixed) . ' ';
        } else {
            $sql .= $this->queryParts->getSelect() . ' ';
        }

        $sql .= $this->queryParts->getFrom()->getQueryPart();
        if (count($this->queryParts->getJoin()) > 0) {
            foreach ($this->queryParts->getJoin() as $join) {
                $sql .= $join->getQueryPart();
            }
        }

        if ($this->queryParts->getExpr() !== null) {
            $sql .= ' WHERE ' . $this->queryParts->getExpr()->getQueryPart() . ' ';
        }

        if (count($this->queryParts->getGroupBy()) !== 0) {
            $sql .= ' GROUP BY ' . implode(', ', $this->queryParts->getGroupBy()) . ' ';
        }

        if ($this->queryParts->getHaving() !== null) {
            $sql .= ' HAVING ' . $this->queryParts->getHaving()->getQueryPart() . ' ';
        }

        $sql .= $this->queryParts->getOrderBy()->getQueryPart();
        $sql .= $this->queryParts->getMaxResult() !== null ? ' LIMIT ' . $this->queryParts->getMaxResult() . ' ' : '';
        $sql .= $this->queryParts->getFirstResult() !== null ? ' OFFSET ' . $this->queryParts->getFirstResult() . ' ' : '';
        foreach ($this->params as $key => $value) {
            $sql = preg_replace("/:{$key}/", $value, (string) $sql);
        }

        return (string) $sql;
    }

    abstract protected function getEntityInfo(string $entityClass): EntityInfo;

    /**
     * @return string[]
     */
    abstract protected function getEntityColumns(string $entityClass): array;

    /**
     * @template T of \Kaa\Component\Database\EntityInterface
     * @kphp-generic T
     * @param class-string<T> $entityClass
     * @return T[]
     */
    abstract public function getResult(string $entityClass): array;

    /**
     * @template T of \Kaa\Component\Database\EntityInterface
     * @kphp-generic T
     * @param class-string<T> $entityClass
     * @return T|null
     * @throws QueryBuilderException
     */
    public function getOneOrNullResult(string $entityClass): EntityInterface|null
    {
        if ($this->queryParts->getSelect() !== null) {
            throw new QueryBuilderException(
                'Using getOneOrNullResult() or getResult() with specified select is not allowed. Use getCustomResult() instead'
            );
        }

        $results = $this->getResult($entityClass);
        if (count($results) > 1) {
            throw new QueryBuilderException(
                'Query builder return more than 1 result. If it is okay, use getResult() instead'
            );
        }

        return count($results) === 0 ? null : $results[0];
    }

    /**
     * @template T of object
     * @kphp-generic T
     * @param callable(mixed):T[] $hydrate
     * @return T[]
     */
    public function getCustomResult(callable $hydrate): array
    {
        $query = $this->getSql();
        $statement = $this->entityManager->_getPdo()->query($query);
        $results = $statement->fetchAll();

        return $hydrate($results);
    }

    public function getSingleScalarResult(): mixed
    {
        $query = $this->getSql();
        $statement = $this->entityManager->_getPdo()->query($query);
        $results = $statement->fetchAll();

        return $results[0];
    }
}
