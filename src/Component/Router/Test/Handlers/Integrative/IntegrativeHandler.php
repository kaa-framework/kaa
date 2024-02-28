<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Integrative;

use Kaa\Component\RequestMapperDecorator\AsJsonResponse;
use Kaa\Component\Router\Attribute\Get;
use Kaa\Component\Router\Attribute\Head;
use Kaa\Component\Router\Attribute\Post;
use Kaa\Component\Router\Attribute\Put;
use Kaa\Component\Router\Attribute\Route;

#[Route('/integration')]
class IntegrativeHandler
{
    #[
        Get('/test'),
        AsJsonResponse,
    ]
    public function first(): int
    {
        return 0;
    }

    #[
        Put('/test/{id}'),
        AsJsonResponse,
    ]
    public function second(): int
    {
        return 1;
    }

    #[
        Post('/test/{id}/current'),
        AsJsonResponse,
    ]
    public function third(): int
    {
        return 2;
    }

    #[
        Head('/test/{id}/{num}'),
        AsJsonResponse,
    ]
    public function fourth(): int
    {
        return 3;
    }

    #[
        Post('/{id}/max'),
        AsJsonResponse,
    ]
    public function fifth(): int
    {
        return 4;
    }

    #[
        Post('/'),
        AsJsonResponse,
    ]
    public function sixth(): int
    {
        return 5;
    }
}
