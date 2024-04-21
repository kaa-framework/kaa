<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query\Expr;

use Kaa\Component\Database\QueryBuilder\Query\ExprInterface;

class OrderBy implements ExprInterface
{
    public const DESC = 'DESC';

    public const ASC = 'ASC';

    /** @var string[] */
    public array $parts = [];

    public function addPart(string $sort, string $order): void
    {
        $this->parts[$sort] = $order;
    }

    public function getQueryPart(): string
    {
        if (count($this->parts) === 0) {
            return '';
        }

        $sql = 'ORDER BY ';
        $parts = [];
        foreach ($this->parts as $sort => $order) {
            $parts[] = $sort . ' ' . $order;
        }

        $sql .= implode(', ', $parts);

        return $sql;
    }
}
