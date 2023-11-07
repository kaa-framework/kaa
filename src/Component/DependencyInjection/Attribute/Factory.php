<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Factory
{
    public function __construct(
        public string $service,
        public string $method = 'invoke',
        public bool $isStatic = false,
    ) {
    }
}
