<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture\Generated;

use Kaa\Component\DependencyInjection\Attribute\Autowire;

class FabricService
{
    public function __construct(
        #[Autowire(parameter: 'app.int')] int $parameter
    ) {
    }

    public function getFive(): int
    {
        return 5;
    }
}
