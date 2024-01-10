<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto\Service;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
readonly class Factory
{
    public function __construct(
        public string $serviceName,
        public string $method,
        public bool $isStatic,
    ) {
    }
}
