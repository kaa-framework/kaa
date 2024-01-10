<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher\Attribute;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS),
]
readonly class EventListener
{
    public function __construct(
        public string $event,
        public int $priority = 0,
        public string $method = 'invoke',
        public string $dispatcher = 'kernel',
    ) {
    }
}
