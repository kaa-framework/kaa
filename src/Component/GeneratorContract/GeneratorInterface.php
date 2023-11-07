<?php

declare(strict_types=1);

namespace Kaa\Component\GeneratorContract;

#[PhpOnly]
interface GeneratorInterface
{
    /**
     * @param mixed[] $config
     */
    public function generate(SharedConfig $sharedConfig, array $config): void;
}
