<?php

namespace Kaa\Component\Database\Hydrator;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class CastFieldHydrator implements FieldHydratorInterface
{
    public function getHydrationCode(string $fieldCode, string $phpType, bool $isNullable, string $valueCode): string
    {
        if ($isNullable) {
            return "{$fieldCode} = {$valueCode} !== null ? ({$phpType}) {$valueCode} : null;";
        }

        return "{$fieldCode} = ({$phpType}) {$valueCode};";
    }
}
