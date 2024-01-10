<?php

namespace Kaa\Component\Generator\Util;

use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\PhpOnly;
use ReflectionNamedType;
use ReflectionType;

#[PhpOnly]
final class Reflection
{
    /**
     * @throws BadTypeException
     */
    public static function namedType(
        ?ReflectionType $type
    ): ReflectionNamedType {
        if (!$type instanceof ReflectionNamedType) {
            throw new BadTypeException('Type must not be a union or an intersection');
        }

        return $type;
    }
}
