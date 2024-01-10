<?php

namespace Kaa\Component\Generator\Util;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder as Finder;
use Kaa\Component\Generator\Exception\FinderException;
use Kaa\Component\Generator\PhpOnly;
use ReflectionClass;

#[PhpOnly]
class ClassFinder
{
    /**
     * @param string[] $scan
     * @param string[] $ignore
     * @param (callable(ReflectionClass): bool)|null $predicate
     * @return ReflectionClass[]
     * @throws Exception
     */
    public static function find(
        array $scan,
        array $ignore = [],
        ?callable $predicate = null,
    ): array {
        Finder::disablePSR4Vendors();

        $classes = [];
        foreach ($scan as $namespaceOrClass) {
            $namespaceOrClass = trim($namespaceOrClass, '\\');
            if (class_exists($namespaceOrClass)) {
                $classes[] = [$namespaceOrClass];
            }

            $classes[] = Finder::getClassesInNamespace($namespaceOrClass, Finder::RECURSIVE_MODE);
        }

        $classes = array_merge(...$classes);
        $reflectionClasses = array_map(
            static fn (string $class) => new ReflectionClass($class),
            $classes,
        );

        $reflectionClasses = array_filter(
            $reflectionClasses,
            static fn (ReflectionClass $c) => self::notInIgnoredNamespace($c, $ignore),
        );

        if ($predicate === null) {
            $reflectionClasses = array_filter($reflectionClasses, $predicate);
        }

        return array_values($reflectionClasses);
    }

    /**
     * @param string[] $ignoreArray
     * @throws FinderException
     */
    private static function notInIgnoredNamespace(ReflectionClass $class, array $ignoreArray): bool
    {
        foreach ($ignoreArray as $ignore) {
            if ($class->getName() === $ignore) {
                return false;
            }
            $ignore = trim($ignore, '\\');
            // Одна звёздочка -> одно вложенное пространство имён
            $ignore = str_replace(['\\', '*'], ['\\\\', '[^\\]+'], $ignore);
            $ignore = '^' . $ignore . '.*';

            if (preg_match("/{$ignore}/", $class->getName(), $matches) === false) {
                throw new FinderException(
                    "Error while matching class name {$class->getName()} with pattern /{$ignore}/",
                );
            }

            if ($matches !== []) {
                return false;
            }
        }

        return true;
    }
}
