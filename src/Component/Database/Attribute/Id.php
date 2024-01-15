<?php

namespace Kaa\Component\Database\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PROPERTY),
]
final readonly class Id
{
    public function __construct(
        public GeneratedType $generatedType = GeneratedType::AutoIncrement,
    ) {
    }
}
