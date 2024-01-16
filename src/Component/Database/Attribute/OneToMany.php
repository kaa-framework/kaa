<?php

namespace Kaa\Component\Database\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PROPERTY),
]
readonly class OneToMany
{
    public function __construct(
        public string $targetEntity,
        public string $mappedBy,
    ) {
    }
}
