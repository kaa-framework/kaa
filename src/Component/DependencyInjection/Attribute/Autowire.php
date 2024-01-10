<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
readonly class Autowire
{
    public function __construct(
        public ?string $service = null,
        public ?string $parameter = null,
    ) {
    }
}
