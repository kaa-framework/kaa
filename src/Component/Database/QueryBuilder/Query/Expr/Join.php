<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query\Expr;

use Kaa\Component\Database\QueryBuilder\Query\Dto\EntityInfo;
use Kaa\Component\Database\QueryBuilder\Query\ExprInterface;

class Join implements ExprInterface
{
    public const INNER_JOIN = 'INNER';

    public const LEFT_JOIN = 'LEFT';

    public const FETCH_JOIN = 'FETCH';

    public const LEFT_FETCH_JOIN = 'LEFT_FETCH';

    private string $currentClassAlias;

    private string $referenceClassAlias;

    private EntityInfo $currentClassInfo;

    private EntityInfo $referenceClassInfo;

    private string $type;

    public function __construct(
        string $currentClassAlias,
        string $referenceClassAlias,
        EntityInfo $currentClassInfo,
        EntityInfo $referenceClassInfo,
        string $type
    ) {
        $this->currentClassAlias = $currentClassAlias;
        $this->referenceClassAlias = $referenceClassAlias;
        $this->currentClassInfo = $currentClassInfo;
        $this->referenceClassInfo = $referenceClassInfo;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getReferenceClassInfo(): EntityInfo
    {
        return $this->referenceClassInfo;
    }

    public function getReferenceClassAlias(): string
    {
        return $this->referenceClassAlias;
    }

    public function getQueryPart(): string
    {
        $sqlPart = '';
        if (array_key_exists($this->currentClassInfo->entityName, $this->referenceClassInfo->manyToOne)) {
            $sqlPart .= $this->currentClassAlias . ".{$this->currentClassInfo->entityIdColumnName} = ";
            $sqlPart .= $this->referenceClassAlias . ".{$this->referenceClassInfo->manyToOne[$this->currentClassInfo->entityName]} ";
        } else {
            $sqlPart .= $this->currentClassAlias . ".{$this->currentClassInfo->manyToOne[$this->referenceClassInfo->entityName]} = ";
            $sqlPart .= $this->referenceClassAlias . ".{$this->referenceClassInfo->entityIdColumnName} ";
        }

        $type = $this->type;
        if ($type === self::LEFT_FETCH_JOIN) {
            $type = self::LEFT_JOIN;
        }

        if ($type == self::FETCH_JOIN) {
            $type = self::INNER_JOIN;
        }

        return $type . ' JOIN' . " {$this->referenceClassInfo->tableName} " . $this->referenceClassAlias . ' ON ' . $sqlPart;
    }
}
