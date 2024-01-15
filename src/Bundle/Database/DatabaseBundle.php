<?php

namespace Kaa\Bundle\Database;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\Database\DatabaseGenerator;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;
use Kaa\Component\Generator\PhpOnly;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
class DatabaseBundle extends DatabaseGenerator implements BundleGeneratorInterface
{
    public function getRootConfigurationKey(): ?string
    {
        return 'database';
    }

    public function getConfiguration(): ?TreeBuilder
    {
        // @formatter:off
        $treeBuilder = new TreeBuilder('database');
        $treeBuilder
            ->getRootNode()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('driver')
                                ->children()
                                    ->scalarNode('type')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('host')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('database')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('user')
                                        ->isRequired()
                                    ->end()
                                    ->scalarNode('password')
                                        ->isRequired()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('scan')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
        // @formatter:on

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getConfigArray(array $config): array
    {
        $services = [];
        $parameters = [];
        $aliases = [];

        foreach ($config as $connectionName => $connectionConfig) {
            $services["database.{$connectionName}"] = [
                'class' => "\Kaa\Generated\Database\EntityManager{$connectionName}",
                'arguments' => [
                    'host' => "%kaa.database.{$connectionName}.host",
                    'database' => "%kaa.database.{$connectionName}.database",
                    'user' => "%kaa.database.{$connectionName}.user",
                    'password' => "%kaa.database.{$connectionName}.password",
                ],
            ];

            $parameters[] = [
                "kaa.database.{$connectionName}.host" => $connectionConfig['driver']['host'],
                "kaa.database.{$connectionName}.database" => $connectionConfig['driver']['database'],
                "kaa.database.{$connectionName}.user" => $connectionConfig['driver']['user'],
                "kaa.database.{$connectionName}.password" => $connectionConfig['driver']['password'],
            ];

            if ($connectionName === 'default') {
                $aliases[EntityManagerInterface::class] = "database.{$connectionName}";
            }
        }

        return [
            'di' => [
                'services' => $services,
                'parameters' => array_replace(...$parameters),
                'aliases' => $aliases,
            ],
        ];
    }
}
