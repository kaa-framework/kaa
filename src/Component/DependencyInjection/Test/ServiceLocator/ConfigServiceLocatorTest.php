<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ServiceLocator;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ArgumentType;
use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\DependencyInjection\ServiceLocator\ConfigServiceLocator;
use Kaa\Component\DependencyInjection\Test\ClassFixture\JustService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\TestFactoryService;

trait Prepare
{
    private function createServiceLocator(
        array $config,
        ServiceCollection $serviceCollection = new ServiceCollection(),
        ParameterCollection $parameterCollection = new ParameterCollection(),
        AliasCollection $aliasCollection = new AliasCollection()
    ): ConfigServiceLocator {
        return new ConfigServiceLocator(
            $config,
            $serviceCollection,
            $parameterCollection,
            $aliasCollection
        );
    }
}

uses(Prepare::class);

it('will throw on service without class', function () {
    $this->createServiceLocator([
        'services' => [
            'app.service_with_no_class' => [],
        ],
    ])->locate();
})->expectException(InvalidServiceDefinitionException::class);

it('will throw if class does not exits', function () {
    $this->createServiceLocator(['services' => ['Not\\Kaa\\Service' => []]])->locate();
})->expectException(InvalidServiceDefinitionException::class);

it('will throw if there are both arguments and factory', function () {
    $this->createServiceLocator([
        'services' => [
            JustService::class => [
                'factory' => [
                    'test' => 'test'
                ],
                'arguments' => [
                    'anotherTest' => 'anotherTest'
                ],
            ],
        ],
    ])->locate();
})->expectException(InvalidServiceDefinitionException::class);

it('will use class as service name', function () {
    $serviceCollection = new ServiceCollection();
    $serviceLocator = new ConfigServiceLocator(
        [
            'services' => [
                JustService::class => [
                ],
            ],
        ],
        $serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();
    expect($serviceCollection->has(JustService::class))->toBe(true);

    $service = $serviceCollection->get(JustService::class);
    expect($service->name)->toBe(JustService::class);
});

it('will use explicit service name', function () {
    $serviceCollection = new ServiceCollection();
    $serviceLocator = new ConfigServiceLocator(
        [
            'services' => [
                'app.just_service' => [
                    'class' => JustService::class,
                ],
            ],
        ],
        $serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();
    expect($serviceCollection->has('app.just_service'))->toBe(true);
    $service = $serviceCollection->get('app.just_service');

    expect($service->name)->toBe('app.just_service');
});

it('will parse factory definition and use invoke as default', function () {
    $serviceCollection = new ServiceCollection();
    $serviceLocator = new ConfigServiceLocator(
        [
            'services' => [
                JustService::class => [
                    'factory' => [
                        'service' => TestFactoryService::class,
                    ],
                ],
            ],
        ],
        $serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();
    expect($serviceCollection->has(JustService::class));
    $service = $serviceCollection->get(JustService::class);

    expect($service->constructionType)->toBe(ConstructionType::Factory)
        ->and($service->factory->serviceName)->toBe(TestFactoryService::class)
        ->and($service->factory->method)->toBe('invoke')
        ->and($service->factory->isStatic)->toBe(false);
});

it('will replace arguments with config arguments', function () {
    $serviceCollection = new ServiceCollection();
    $serviceLocator = new ConfigServiceLocator(
        [
            'parameters' => [
                'app.int' => 10,
            ],

            'services' => [
                'app.just_service' => [
                    'class' => JustService::class,
                    'arguments' => [
                        'parameter' => '%app.int',
                        'justService2' => '@' . JustService::class,
                    ],
                ],
            ],
        ],
        $serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();

    expect($serviceCollection->has('app.just_service'))->toBe(true);
    $service = $serviceCollection->get('app.just_service');

    expect($service->constructionType)->toBe(ConstructionType::Constructor)
        ->and($service->arguments[0]->type)->toBe(ArgumentType::Parameter)
        ->and($service->arguments[0]->name)->toBe('app.int')
        ->and($service->arguments[1]->type)->toBe(ArgumentType::Service)
        ->and($service->arguments[1]->name)->toBe(JustService::class);
});

it('will throw an error when using service as factory for itself', function () {
    $serviceCollection = new ServiceCollection();
    $serviceLocator = new ConfigServiceLocator(
        [
            'services' => [
                JustService::class => [
                    'factory' => [
                        'service' => JustService::class,
                    ],
                ],
            ],
        ],
        $serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();
})->expectException(InvalidServiceDefinitionException::class);

it('will throw if invalid starting symbol for param if config', function () {
    $serviceCollection = new ServiceCollection();
    $serviceLocator = new ConfigServiceLocator(
        [
            'parameters' => [
                'app.int' => 10,
            ],

            'services' => [
                'app.just_service' => [
                    'class' => JustService::class,
                    'arguments' => [
                        'parameter' => '?app.int',
                        'justService2' => '&' . JustService::class,
                    ],
                ],
            ],
        ],
        $serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();
})->expectException(InvalidServiceDefinitionException::class);
