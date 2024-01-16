<?php

namespace Kaa\Component\Database\Dto;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class OneToManyMetadata
{
    public function __construct(
        public readonly string $fieldName,
        public readonly string $targetEntity,
        public readonly string $targetEntityClassName,
        public readonly string $referenceFieldName,
        public ?string $targetEntityTable = null,
        public ?string $referenceColumnName = null,
        public ?string $targetEntityIdColumnName = null,
    ) {
    }
}
