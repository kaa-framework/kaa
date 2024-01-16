<?php

namespace Kaa\Component\Database\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PROPERTY),
]
final readonly class ManyToOne
{
    public function __construct(
        public string $targetEntity,
        public ?string $columnName = null,
        public bool $nullable = false
    ) {
    }
}
