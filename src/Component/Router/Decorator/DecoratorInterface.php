<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\GeneratorContract\NewInstanceGeneratorInterface;
use ReflectionMethod;

interface DecoratorInterface
{
    public function getType(): DecoratorType;

    public function getPriority(): int;

    /**
     * Генерирует код, который надо вызвать до/после вызова метода
     */
    public function decorate(
        ReflectionMethod $decoratedMethod,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string;
}
