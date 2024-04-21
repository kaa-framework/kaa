<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query\Expr;

use Kaa\Component\Database\QueryBuilder\Query\ExprInterface;

class From implements ExprInterface
{
    private string $tableName;

    private string $alias;

    public function __construct(
        string $tableName,
        string $alias
    ) {
        $this->tableName = $tableName;
        $this->alias = $alias;
    }

    public function getTable(): string
    {
        return $this->tableName;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getQueryPart(): string
    {
        return 'FROM ' . $this->tableName . ' ' . $this->alias . ' ';
    }
}
