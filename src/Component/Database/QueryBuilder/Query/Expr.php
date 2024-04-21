<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query;

use Kaa\Component\Database\QueryBuilder\Query\Expr\ExprAnd;
use Kaa\Component\Database\QueryBuilder\Query\Expr\ExprOr;

class Expr implements ExprInterface
{
    private string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function getQueryPart(): string
    {
        return $this->expression;
    }

    public static function e(string $expr): self
    {
        return new self($expr);
    }

    /**
     * @param Expr[] $expr
     */
    public static function and(array $expr): ExprAnd
    {
        return new ExprAnd($expr);
    }

    /**
     * @param Expr[] $expr
     */
    public static function or(array $expr): ExprOr
    {
        return new ExprOr($expr);
    }
}
