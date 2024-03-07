<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\Writer;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\ServiceLocator\AttributesToConfigParser;
use Kaa\Component\DependencyInjection\ServiceLocator\ConfigServiceLocator;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Generated\FabricService;
use Kaa\Component\DependencyInjection\Writer\ContainerWriter;
use Kaa\Component\Generator\SharedConfig;

beforeAll(function () {
    $sharedConfig = new SharedConfig(dirname(__DIR__, 5) . '/generated');
    $config = [
        'scan' => [
            'Kaa\Component\DependencyInjection\Test\ClassFixture\Generated'
        ],
        'ignore' => [
            'Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored',
        ],
        'parameters' => [
            'app.int' => 10,
        ],
        'services' => [
            'app.fabric_service' => [
                'class' => FabricService::class,
                'arguments' => [
                    'parameter' => '%app.int',
                ],
            ],
        ]
    ];
    $config = (new AttributesToConfigParser($config))->getConfig();
    $serviceCollection = new ServiceCollection();
    $parameterCollection = new ParameterCollection();
    $aliasCollection = new AliasCollection();
    (new ConfigServiceLocator($config, $serviceCollection, $parameterCollection, $aliasCollection))->locate();
    (new ContainerWriter($sharedConfig, $serviceCollection, $parameterCollection, $aliasCollection))->write();
});

test('writes correctly', function () {
    expect(class_exists('Kaa\Generated\DependencyInjection\Container'))->toBe(true);
});
