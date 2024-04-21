<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query;

interface ExprInterface
{
    public function getQueryPart(): string;
}
