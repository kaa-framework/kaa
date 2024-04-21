<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder\Query\Dto;

class EntityInfo
{
    public string $entityName;

    public string $tableName;

    public string $entityIdColumnName;

    /** @var string[] */
    public array $oneToMany = [];

    /** @var string[] */
    public array $manyToOne = [];

    /** @var string[] */
    public array $referenceColumns = [];

    public int $columnsCount;

    /**
     * @param string[] $oneToMany
     * @param string[] $manyToOne
     * @param string[] $referenceColumns
     */
    public function __construct(
        string $entityName,
        string $tableName,
        string $entityIdColumnName,
        array $oneToMany,
        array $manyToOne,
        array $referenceColumns,
        int $columnsCount
    ) {
        $this->entityName = $entityName;
        $this->tableName = $tableName;
        $this->entityIdColumnName = $entityIdColumnName;
        $this->oneToMany = $oneToMany;
        $this->manyToOne = $manyToOne;
        $this->referenceColumns = $referenceColumns;
        $this->columnsCount = $columnsCount;
    }
}
