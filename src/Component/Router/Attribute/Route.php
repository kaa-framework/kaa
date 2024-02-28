<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD),
    PhpOnly,
]
readonly class Route
{
    public function __construct(
        public string $route,
        public ?string $method = null,
    ) {
    }
}
