<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored;

class IgnoredService
{
    public function __toString(): string
    {
        return 'IgnoredClass';
    }
}
