<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Attribute;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Router\HttpMethod;

#[
    Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE),
    PhpOnly,
]
readonly class Put extends Route
{
    public function __construct(
        string $route,
    ) {
        parent::__construct($route, HttpMethod::PUT);
    }
}
