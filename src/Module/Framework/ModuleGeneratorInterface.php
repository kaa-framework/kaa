<?php

declare(strict_types=1);

namespace Kaa\Module\Framework;

use Kaa\Component\GeneratorContract\GeneratorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

interface ModuleGeneratorInterface extends GeneratorInterface
{
    public function getRootConfigurationKey(): string;

    public function getConfiguration(): TreeBuilder;

    public function getPriority(): int;

    public function getConfigArray(): mixed;
}
