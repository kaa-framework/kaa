<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto\Service;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class Argument
{
    public function __construct(
        public ArgumentType $type,
        public string $name,
    ) {
    }
}
