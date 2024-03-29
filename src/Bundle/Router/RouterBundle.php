<?php

declare(strict_types=1);

namespace Kaa\Bundle\Router;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Bundle\Router\Writer\ListenerWriter;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\HttpKernel\HttpKernelEvents;
use Kaa\Component\Router\RouterGenerator;
use Kaa\Component\Router\RouterInterface;
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
        // @formatter:off
        $treeBuilder = new TreeBuilder('router');
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->arrayNode('scan')
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('prefixes')
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('routes')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('route')->end()
                                ->scalarNode('method')->end()
                                ->scalarNode('service')->end()
                                ->scalarNode('classMethod')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        // @formatter:on

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function getConfigArray(array $config): array
    {
        return [
            'di' => [
                'services' => [
                    RouterInterface::class => [
                        'class' => 'Kaa\Generated\Router\Router',
                    ],

                    '\Kaa\Generated\Router\FindActionListener' => [
                        'class' => '\Kaa\Generated\Router\FindActionListener',
                    ],
                ],
            ],

            'dispatcher' => [
                'listeners' => [
                    [
                        'service' => '\Kaa\Generated\Router\FindActionListener',
                        'event' => HttpKernelEvents::FIND_ACTION,
                    ],
                ],
            ],
        ];
    }
}
