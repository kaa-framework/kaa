<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Ignore;

use Kaa\Component\Router\Attribute\Route;

#[Route('/error', 'POST')]
class ThrowHandler
{
    public function nothing(): void
    {
    }
}
