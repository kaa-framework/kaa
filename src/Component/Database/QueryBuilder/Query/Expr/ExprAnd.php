<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query\Expr;

use Kaa\Component\Database\QueryBuilder\Query\Expr;

class ExprAnd extends Expr
{
    /** @var Expr[] */
    private array $expressions;

    /**
     * @param Expr[] $expressions
     */
    public function __construct(array $expressions)
    {
        parent::__construct('AND');
        $this->expressions = $expressions;
    }

    public function getQueryPart(): string
    {
        $sqList = array_map(static fn ($e): string => $e->getQueryPart(), $this->expressions);

        return '(' . implode(' AND ', $sqList) . ')';
    }
}
