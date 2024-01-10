<?php

declare(strict_types=1);

namespace Kaa\Bundle\Framework;

use Kaa\Component\Generator\GeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
interface BundleGeneratorInterface extends GeneratorInterface
{
    public function getRootConfigurationKey(): ?string;

    public function getConfiguration(): ?TreeBuilder;

    public function getPriority(): int;

    /**
     * @return mixed[]
     */
    public function getConfigArray(): array;
}
