<?php

namespace Kaa\Component\Database\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_CLASS),
]
final readonly class Entity
{
    public function __construct(
        public ?string $table = null,
    ) {
    }
}
