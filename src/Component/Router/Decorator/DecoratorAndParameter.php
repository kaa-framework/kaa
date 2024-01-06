<?php

namespace Kaa\Component\Router\Decorator;

use ReflectionParameter;

readonly class DecoratorAndParameter
{
    public function __construct(
        public DecoratorInterface $decorator,
        public ?ReflectionParameter $parameter,
    ) {
    }
}
