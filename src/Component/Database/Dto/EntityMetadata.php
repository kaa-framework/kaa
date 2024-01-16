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
        public string $idColumnName,
        public string $idFieldName,

        /** @var FieldMetadata[] */
        public array $fields,

        /** @var ManyToOneMetadata[] */
        public array $manyToOne,
    ) {
    }
}
