<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Dto;

use Kaa\Component\GeneratorContract\PhpOnly;

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
