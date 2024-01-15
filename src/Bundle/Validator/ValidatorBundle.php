<?php

declare(strict_types=1);

namespace Kaa\Bundle\Validator;

use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\ValidatorGenerator;
use Kaa\Component\Validator\ValidatorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
readonly class ValidatorBundle extends ValidatorGenerator implements BundleGeneratorInterface
{
    public function getRootConfigurationKey(): string
    {
        return 'validator';
    }

    public function getConfiguration(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('validator');
        // @formatter:off
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->arrayNode('scan')
                        ->scalarPrototype()->end()
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
        return [
            'di' => [
                'services' => [
                    ValidatorInterface::class => [
                        'class' => 'Kaa\Generated\Validator\Validator',
                    ],
                ],
            ],
        ];
    }
}
