<?php

namespace Kaa\Component\Database\Hydrator;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
interface FieldHydratorInterface
{
    public function getHydrationCode(
        string $fieldCode,
        string $phpType,
        bool $isNullable,
        string $valueCode
    ): string;
}
