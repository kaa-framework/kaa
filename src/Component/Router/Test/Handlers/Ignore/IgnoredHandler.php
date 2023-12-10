<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Handlers\Ignore;

use Kaa\Component\Router\Attribute\Get;

class IgnoredHandler
{
    #[Get('/ignore')]
    public function ignore(): int
    {
        return 1;
    }
}
