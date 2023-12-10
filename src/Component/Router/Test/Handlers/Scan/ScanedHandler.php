<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Scan;

use Kaa\Component\Router\Attribute\Get;
use Kaa\Component\Router\Attribute\Post;
use Kaa\Component\Router\Attribute\Route;

#[Route('/test/')]
class ScanedHandler
{
    #[
        Get('/healthcheck'),
        Post('/posthealthcheck'),
    ]
    public function healthcheck(): int
    {
        return 0;
    }
}
