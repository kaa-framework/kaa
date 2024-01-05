<?php

declare(strict_types=1);

namespace Kaa\Bundle\Router;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\Router\RouterGenerator;
use Kaa\Component\Router\RouterInterface;
use Kaa\HttpKernel\HttpKernelEvents;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
readonly class RouterBundle extends RouterGenerator implements BundleGeneratorInterface
{
    public function generate(SharedConfig $sharedConfig, $config): void
    {
        parent::generate($sharedConfig, $config);

        (new ListenerWriter($sharedConfig))->write();
    }

    public function getRootConfigurationKey(): string
    {
        return 'router';
    }

    public function getConfiguration(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('router');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('scan')
            ->prototype('scalar')
            ->end()
            ->arrayNode('prefixes')
            ->prototype('scalar')
            ->end()
            ->arrayNode('routes')
            ->arrayPrototype()
            ->children()
            ->scalarNode('route')->end()
            ->scalarNode('method')->end()
            ->scalarNode('service')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function getConfigArray(): mixed
    {
        return [
            'di' => [
                'serivces' => [
                    RouterInterface::class => [
                        'class' => 'Kaa\Generated\Router\Router',
                    ],
                ],
            ],

            'dispatcher' => [
                'listeners' => [
                    'service' => '\Kaa\Generated\Router\FindActionListener',
                    'event' => HttpKernelEvents::FIND_ACTION,
                ],
            ],
        ];
    }
}
