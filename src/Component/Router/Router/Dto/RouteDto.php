<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Router\Dto;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class RouteDto
{
    public function __construct(
        public string $route,
        public string $method,
        public string $name,
        public string $className,
        public string $methodName,
    ) {
    }
}
