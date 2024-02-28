<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\Generator\PhpOnly;
use ReflectionParameter;

#[PhpOnly]
readonly class DecoratorAndParameter
{
    public function __construct(
        public DecoratorInterface $decorator,
        public ?ReflectionParameter $parameter,
    ) {
    }
}
