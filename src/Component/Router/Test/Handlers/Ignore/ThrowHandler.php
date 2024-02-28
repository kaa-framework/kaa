<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Ignore;

use Kaa\Component\Router\Attribute\Put;
use Kaa\Component\Router\Attribute\Route;
use Kaa\Component\Router\Test\Services\TestService;

#[Route('/error', 'POST')]
class ThrowHandler
{
    #[Put('/throw')]
    public function nothing(
        int $id,
        TestService $testService
    ): int {
        return $id;
    }
}
