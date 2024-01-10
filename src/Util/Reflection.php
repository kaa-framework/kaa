<?php

namespace Kaa\Util;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Util\Exception\BadParameterTypeException;
use ReflectionNamedType;
use ReflectionType;

#[PhpOnly]
final class Reflection
{
    /**
     * @throws BadParameterTypeException
     */
    public static function namedType(
        ?ReflectionType $type
    ): ReflectionNamedType {
        if (!$type instanceof ReflectionNamedType) {
            throw new BadParameterTypeException('Parameter must not be a union or an intersection');
        }

        return $type;
    }
}
