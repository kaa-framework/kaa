<?php

declare(strict_types=1);

namespace Kaa\Module\Router;

use Kaa\Component\Router\RouterGenerator;
use Kaa\Component\Router\RouterInterface;
use Kaa\Module\Framework\ModuleGeneratorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

readonly class RouterModule extends RouterGenerator implements ModuleGeneratorInterface
{
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
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('prefixes')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('routes')
            ->prototype('array')
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
        return 20;
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
        ];
    }
}
