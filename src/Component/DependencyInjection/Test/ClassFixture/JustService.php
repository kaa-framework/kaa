<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture;

use Kaa\Component\DependencyInjection\Attribute\Factory;

#[Factory(TestFactoryService::class)]
class JustService
{
    public function __construct(
        int $parameter,
        self $justService2,
    ) {
    }

    public function getOne(): int
    {
        return 1;
    }
}
