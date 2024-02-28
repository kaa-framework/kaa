<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Scan;

use Kaa\Component\DependencyInjectionDecorator\Inject;
use Kaa\Component\RequestMapperDecorator\AsJsonResponse;
use Kaa\Component\Router\Attribute\Get;
use Kaa\Component\Router\Attribute\Post;
use Kaa\Component\Router\Attribute\Route;
use Kaa\Component\Router\Test\Services\TestService;

#[Route('/test')]
class ScanedHandler
{
    #[
        Get('/healthcheck'),
        Post('/posthealthcheck'),
        AsJsonResponse,
    ]
    public function healthcheck(
        #[Inject]
        TestService $testService
    ): int {
        return $testService->returnZero();
    }
}
