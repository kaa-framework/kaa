<?php

namespace Kaa\Bundle\KTemplate;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use KTemplate\Context;
use KTemplate\Engine;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
class KTemplateBundle implements BundleGeneratorInterface
{
    public function getRootConfigurationKey(): string
    {
        return 'ktemplate';
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
                    ->scalarNode('url')
                        ->isRequired()
                    ->end()
                    ->scalarNode('template_path')
                        ->isRequired()
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
        $parameters = [
            'kaa.ktemplate.path' => $config['path'],
            'kaa.ktemplate.url' => $config['url'],
            'kaa.ktemplate.template_path' => $config['template_path'],
        ];

        return [
            'di' => [
                'services' => [
                    KTemplateFactory::class => [
                        'arguments' => [
                            'context' => '@' . Context::class,
                            'url' => '%kaa.ktemplate.url',
                            'templatePath' => '%kaa.ktemplate.template_path'
                        ],
                    ],

                    Context::class => [],

                    KTemplateController::class => [
                        'arguments' => [
                            'path' => '%kaa.ktemplate.path',
                        ]
                    ],

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
                        'route' => '/kaa_get_file/{fileName}/{fileType}',
                        'method' => 'GET',
                        'service' => KTemplateController::class,
                        'classMethod' => 'getFile'
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
