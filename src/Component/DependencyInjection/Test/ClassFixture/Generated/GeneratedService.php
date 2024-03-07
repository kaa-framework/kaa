<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture\Generated;

use Kaa\Component\DependencyInjection\Attribute\Autowire;

class GeneratedService
{
    public function __construct(
        #[Autowire(service: 'app.fabric_service')] FabricService $service
    ) {
    }

    public function __toString(): string
    {
        return 'GeneratedService';
    }

    public function getZero(
    ): string {
        return 'zero';
    }
}
