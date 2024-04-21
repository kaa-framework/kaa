<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query;

use Kaa\Component\Database\QueryBuilder\Query\Dto\EntityInfo;
use Kaa\Component\Database\QueryBuilder\Query\Expr\From;
use Kaa\Component\Database\QueryBuilder\Query\Expr\Join;
use Kaa\Component\Database\QueryBuilder\Query\Expr\OrderBy;

class QueryParts
{
    private ?string $select = null;

    private From $from;

    /** @var Join[] */
    private array $join = [];

    private ?ExprInterface $expr = null;

    /** @var string[] */
    private array $groupBy = [];

    private ?ExprInterface $having = null;

    private OrderBy $orderBy;

    private ?int $firstResult = null;

    private ?int $maxResult = null;

    public function __construct(string $tableName, string $alias)
    {
        $this->from = new From($tableName, $alias);
        $this->orderBy = new OrderBy();
    }

    public function setSelect(string $select): void
    {
        $this->select = $select;
    }

    public function join(
        string $currentClassAlias,
        string $referenceAlias,
        EntityInfo $currentClassInfo,
        EntityInfo $referenceClassInfo,
        string $type
    ): void {
        $this->join[] = new Join($currentClassAlias, $referenceAlias, $currentClassInfo, $referenceClassInfo, $type);
    }

    public function setExpression(ExprInterface $expr): void
    {
        $this->expr = $expr;
    }

    /**
     * @param string[] $columns
     */
    public function setGroupBy(array $columns): void
    {
        $this->groupBy = $columns;
    }

    public function setHaving(ExprInterface $expr): void
    {
        $this->having = $expr;
    }

    public function setFirstResult(int $offset): void
    {
        $this->firstResult = $offset;
    }

    public function addOrderBy(string $sort, string $order): void
    {
        $this->orderBy->addPart($sort, $order);
    }

    public function setMaxResult(int $limit): void
    {
        $this->maxResult = $limit;
    }

    public function getSelect(): ?string
    {
        return $this->select;
    }

    public function getFrom(): From
    {
        return $this->from;
    }

    /**
     * @return Join[]
     */
    public function getJoin(): array
    {
        return $this->join;
    }

    public function getExpr(): ?ExprInterface
    {
        return $this->expr;
    }

    /**
     * @return string[]
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    public function getHaving(): ?ExprInterface
    {
        return $this->having;
    }

    public function getOrderBy(): OrderBy
    {
        return $this->orderBy;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function getMaxResult(): ?int
    {
        return $this->maxResult;
    }
}
