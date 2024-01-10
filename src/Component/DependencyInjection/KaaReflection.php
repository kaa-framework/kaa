<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection;

use Kaa\Component\Generator\PhpOnly;
use ReflectionClass;

#[PhpOnly]
final class KaaReflection
{
    /**
     * @return string[]
     */
    public static function getClassParents(ReflectionClass $reflectionClass): array
    {
        $interfaces = [[$reflectionClass->name]];
        if ($reflectionClass->getParentClass() !== false) {
            $interfaces[] = self::getClassParents($reflectionClass->getParentClass());
        }

        foreach ($reflectionClass->getInterfaces() as $interface) {
            $interfaces[] = self::getClassParents($interface);
        }

        return array_merge(...$interfaces);
    }
}
