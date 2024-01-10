<?php

namespace Kaa\Bundle\HttpKernel;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\HttpKernel\HttpKernel;

#[PhpOnly]
class HttpKernelBundle implements BundleGeneratorInterface
{
    public function getRootConfigurationKey(): null
    {
        return null;
    }

    public function getConfiguration(): null
    {
        return null;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getConfigArray(): array
    {
        return [
            'di' => [
                'services' => [
                    HttpKernel::class => [
                        'arguments' => [
                            'eventDispatcher' => '@kernel.dispatcher.kernel',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $config
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
    }
}
