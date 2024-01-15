<?php

namespace Kaa\Component\Database\Serializer;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
interface FieldSerializerInterface
{
    public function getSerializationCode(
        string $fieldCode,
        string $phpType,
        bool $isNullable,
    ): string;
}
