<?php

namespace Kaa\Component\Database\Serializer;

use DateTimeImmutable;
use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class DateTimeImmutableFieldSerializer implements FieldSerializerInterface
{
    /**
     * @throws DatabaseGeneratorException
     */
    public function getSerializationCode(string $fieldCode, string $phpType, bool $isNullable): string
    {
        if ($phpType !== DateTimeImmutable::class) {
            throw new DatabaseGeneratorException(self::class . ' supports only DateTimeImmutable fields');
        }

        if ($isNullable) {
            return "{$fieldCode} !== null ? \"'\" . {$fieldCode}->format('Y-m-d H:i:s') . \"'\" : null";
        }

        return "\"'\" . {$fieldCode}->format('Y-m-d H:i:s') . \"'\"";
    }
}
