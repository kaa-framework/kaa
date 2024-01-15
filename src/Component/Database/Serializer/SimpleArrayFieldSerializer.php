<?php

namespace Kaa\Component\Database\Serializer;

use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class SimpleArrayFieldSerializer implements FieldSerializerInterface
{
    /**
     * @throws DatabaseGeneratorException
     */
    public function getSerializationCode(string $fieldCode, string $phpType, bool $isNullable): string
    {
        if ($phpType !== 'array') {
            throw new DatabaseGeneratorException(self::class . ' supports only array fields');
        }

        return "\"'\" . implode(',', {$fieldCode}) . \"'\"";
    }
}
