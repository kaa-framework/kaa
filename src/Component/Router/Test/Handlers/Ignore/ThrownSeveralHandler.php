<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Ignore;

use Kaa\Component\Router\Attribute\Route;

#[
    Route('/no-error'),
    Route('/error'),
]
class ThrownSeveralHandler
{
    public function nothing(): void
    {
    }
}
