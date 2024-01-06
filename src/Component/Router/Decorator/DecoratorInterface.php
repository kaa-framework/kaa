<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\GeneratorContract\NewInstanceGeneratorInterface;
use ReflectionMethod;
use ReflectionParameter;

interface DecoratorInterface
{
    public function getType(): DecoratorType;

    public function getPriority(): int;

    /**
     * Генерирует код, который надо вызвать до/после вызова метода
     */
    public function decorate(
        ReflectionMethod $decoratedMethod,
        ?ReflectionParameter $reflectionParameter,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string;
}
