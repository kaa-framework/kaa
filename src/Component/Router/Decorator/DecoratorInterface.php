<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use ReflectionMethod;
use ReflectionParameter;

#[PhpOnly]
interface DecoratorInterface
{
    public function getType(): DecoratorType;

    public function getPriority(): int;

    /**
     * Генерирует код, который надо вызвать до/после вызова метода
     */
    public function decorate(
        ReflectionMethod $decoratedMethod,
        ?ReflectionParameter $parameter,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string;
}
