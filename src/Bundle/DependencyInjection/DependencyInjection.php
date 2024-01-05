<?php

declare(strict_types=1);

namespace Kaa\Bundle\DependencyInjection;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\DependencyInjection\ContainerGenerator;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

readonly class DependencyInjection extends ContainerGenerator implements BundleGeneratorInterface
{
    public function getRootConfigurationKey(): string
    {
        return 'di';
    }

    public function getConfiguration(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('router');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('scan')
            ->isRequired()
            ->prototype('scalar')
            ->end()
            ->arrayNode('ignore')
            ->isRequired()
            ->prototype('scalar')
            ->end()
            ->arrayNode('parameters')
            ->useAttributeAsKey('name')
            ->scalarPrototype()
            ->end()
            ->arrayNode('services')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('class')->end()
            ->arrayNode('arguments')
            ->useAttributeAsKey('name')
            ->scalarPrototype()
            ->end()
            ->booleanNode('singleton')->end()
            ->arrayNode('factory')
            ->arrayPrototype()
            ->children()
            ->scalarNode('service')->end()
            ->scalarNode('method')->end()
            ->booleanNode('static')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('aliases')
            ->useAttributeAsKey('name')
            ->scalarPrototype()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function getConfigArray(): mixed
    {
        return [];
    }
}
