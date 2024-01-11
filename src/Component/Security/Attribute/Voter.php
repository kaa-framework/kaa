<?php

namespace Kaa\Component\Security\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_CLASS),
]
final readonly class Voter
{
    public function __construct(
        public string $attribute,
    ) {
    }
}
