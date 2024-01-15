<?php

namespace Kaa\Component\Database\Serializer;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class CastFieldSerializer implements FieldSerializerInterface
{
    public function getSerializationCode(string $fieldCode, string $phpType, bool $isNullable): string
    {
        if ($phpType === 'string') {
            if ($isNullable) {
                return "{$fieldCode} !== null ? \"'\" . {$fieldCode} . \"'\" : {$fieldCode}";
            }

            return "\"'\" . {$fieldCode} . \"'\"";
        }

        return $fieldCode;
    }
}
