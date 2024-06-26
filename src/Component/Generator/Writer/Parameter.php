<?php

namespace Kaa\Component\Generator\Writer;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class Parameter
{
    public function __construct(
        public string $type,
        public string $name,
        public bool $nullable = false,
        public mixed $defaultValue = None::None,
    ) {
    }
}
