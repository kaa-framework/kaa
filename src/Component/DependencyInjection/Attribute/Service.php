<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Service
{
    public function __construct(
        public bool $singleton = true,
        /** @var string[]|string */
        public array|string $aliases = [],
    ) {
    }
}
