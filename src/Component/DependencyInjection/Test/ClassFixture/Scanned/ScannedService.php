<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture\Scanned;

use Kaa\Component\DependencyInjection\Attribute\Autowire;
use Kaa\Component\DependencyInjection\Test\ClassFixture\JustService;

class ScannedService
{
    public function __construct(
        #[Autowire(service: 'app.service')] JustService $justService,
        #[Autowire(parameter: 'app.parameter')] int $param,
    ) {
    }

    public function __toString(): string
    {
        return self::class;
    }
}
