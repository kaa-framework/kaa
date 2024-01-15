<?php

namespace Kaa\Component\Database\Dto;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class EntityMetadata
{
    public function __construct(
        public string $entityClass,
        public string $className,
        public string $tableName,
        /** @var FieldMetadata[] */
        public array $fields,
        public string $idColumnName,
        public string $idFieldName,
    ) {
    }
}
