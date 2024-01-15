<?php

namespace Kaa\Component\Database\Hydrator;

use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class SimpleArrayFieldHydrator implements FieldHydratorInterface
{
    /**
     * @throws DatabaseGeneratorException
     */
    public function getHydrationCode(string $fieldCode, string $phpType, bool $isNullable, string $valueCode): string
    {
        if ($phpType !== 'array') {
            throw new DatabaseGeneratorException(self::class . ' supports only array fields');
        }

        return "{$fieldCode} = {$valueCode} !== '' ? explode(',', {$valueCode}) : [];";
    }
}
