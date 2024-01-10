<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class DecoratedMethod
{
    public function __construct(
        public string $class,
        public string $service,
        public string $method,
        public string $decoratedMethodName,
    ) {
    }
}
