<?php

namespace Kaa\Component\Database\Hydrator;

use DateTimeImmutable;
use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class DateTimeImmutableFieldHydrator implements FieldHydratorInterface
{
    /**
     * @throws DatabaseGeneratorException
     */
    public function getHydrationCode(string $fieldCode, string $phpType, bool $isNullable, string $valueCode): string
    {
        if ($phpType !== DateTimeImmutable::class) {
            throw new DatabaseGeneratorException(self::class . ' supports only DateTimeImmutable fields');
        }

        if ($isNullable) {
            return "{$fieldCode} = {$valueCode} !== null ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) {$valueCode}) : null;";
        }

        return "{$fieldCode} = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) {$valueCode});";
    }
}
