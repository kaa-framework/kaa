<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection;

use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class InstanceProvider implements NewInstanceGeneratorInterface
{
    public function generate(string $nameOrAlias, string $class): string
    {
        return "\Kaa\Generated\DependencyInjection\Container::get('{$nameOrAlias}', \\{$class}::class)";
    }
}
