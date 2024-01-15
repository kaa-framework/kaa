<?php

namespace Kaa\Component\Database\Dto;

use Kaa\Component\Database\Attribute\Type;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class FieldMetadata
{
    public function __construct(
        public string $name,
        public string $columnName,
        public Type $type,
        public string $phpType,
        public bool $isNullable,
    ) {
    }
}
