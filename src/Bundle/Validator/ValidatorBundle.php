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
        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('scan')
            ->prototype('scalar')
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 50;
    }

    public function getConfigArray(): array
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
