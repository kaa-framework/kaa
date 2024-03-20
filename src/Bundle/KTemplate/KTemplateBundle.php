<?php

namespace Kaa\Bundle\KTemplate;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use KTemplate\Context;
use KTemplate\Engine;
use KTemplate\FilesystemLoader;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
class KTemplateBundle implements BundleGeneratorInterface
{
    public function getRootConfigurationKey(): null
    {
        return null;
    }

    public function getConfiguration(): ?TreeBuilder
    {
        // @formatter:off
        $treeBuilder = new TreeBuilder('ktemplate');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('path')
            ->isRequired()
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
        $parameters = [
            'kaa.ktemplate.path' => $config['path'],
            'kaa.ktemplate.url' => $config['url'],
        ];

        return [
            'di' => [
                'services' => [
                    KTemplateFactory::class => [
                        'arguments' => [
                            'context' => '@' . Context::class,
                            'filesystemLoader' => '@' . FilesystemLoader::class,
                            'url' => '%kaa.ktemplate.url',
                        ],
                    ],

                    FilesystemLoader::class => [
                        'arguments' => [
                            'paths' => '%kaa.ktemplate.path',
                        ],
                    ],

                    Context::class => [],

                    KTemplateController::class => [],

                    Engine::class => [
                        'factory' => [
                            'service' => KTemplateFactory::class,
                        ]
                    ],
                ],

                'parameters' => $parameters,
            ],

            'router' => [
                'routes' => [
                    [
                        'route' => '/css/{fileName}',
                        'method' => 'GET',
                        'service' => KTemplateController::class,
                        'classMethod' => 'getCss'
                    ],
                ],
            ]
        ];
    }

    /**
     * @param mixed[] $config
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
    }
}
