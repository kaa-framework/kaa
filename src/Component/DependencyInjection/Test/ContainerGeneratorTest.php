<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test;

use Kaa\Component\DependencyInjection\ContainerGenerator;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Generated\FabricService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Generated\GeneratedService;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Generated\DependencyInjection\Container;

beforeAll(function () {
    $sharedConfig = new SharedConfig(dirname(__DIR__, 4) . '/generated');
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
    (new ContainerGenerator())->generate($sharedConfig, $config);
});

test('correctly created', function () {
    expect(class_exists('Kaa\Generated\DependencyInjection\Container'))->toBe(true);
});

test('get class by alias', function () {
    $container = new Container();
    expect($container->get('app.fabric_service', FabricService::class))->toBeInstanceOf(FabricService::class)
        ->and($container->get(FabricService::class, FabricService::class))->toBeInstanceOf(FabricService::class)
        ->and($container->get(GeneratedService::class, GeneratedService::class))->toBeInstanceOf(GeneratedService::class);
});
