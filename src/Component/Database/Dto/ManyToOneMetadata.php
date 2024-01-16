<?php

namespace Kaa\Component\Database\Dto;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class ManyToOneMetadata
{
    public function __construct(
        public string $fieldName,
        public string $targetEntity,
        public string $targetEntityClassName,
        public string $columnName,
        public bool $isNullable,
    ) {
    }
}
