<?php

declare(strict_types=1);

namespace Kaa\Component\RequestMapperDecorator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\RequestMapperDecorator\Exception\DecoratorException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[PhpOnly]
class SetUtil
{
    /**
     * Генерирует строчку кода, которая устанавливает свойству $reflectionProperty объекта с именем $objectName
     * значение $value
     *
     * @param string $value Может быть строкой с константой, вызовом метода, конструктора и т.д.
     * @throws ReflectionException|DecoratorException
     */
    public static function generateSetStatement(
        ReflectionProperty $reflectionProperty,
        string $modelName,
        string $value,
    ): string {
        if ($reflectionProperty->isPublic()) {
            return sprintf('$%s->%s = %s;', $modelName, $reflectionProperty->name, $value);
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $setterMethodName = self::getMethodNameWithRightCase(
            $reflectionClass,
            'set' . $reflectionProperty->name
        );

        if ($setterMethodName === null) {
            throw new DecoratorException(
                sprintf(
                    'Property %s::%s is private and it`s class does not have setter method',
                    $reflectionClass->name,
                    $reflectionProperty->name,
                )
            );
        }

        if (!$reflectionClass->getMethod($setterMethodName)->isPublic()) {
            throw new DecoratorException(
                sprintf(
                    'Property %s::%s is private and it`s setter %s is also private',
                    $reflectionClass->name,
                    $reflectionProperty->name,
                    $reflectionProperty->name,
                )
            );
        }

        return sprintf('$%s->%s(%s);', $modelName, $setterMethodName, $value);
    }

    private static function getMethodNameWithRightCase(ReflectionClass $reflectionClass, string $methodName): ?string
    {
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (strcasecmp($reflectionMethod->name, $methodName) === 0) {
                return $reflectionMethod->name;
            }
        }

        return null;
    }
}
