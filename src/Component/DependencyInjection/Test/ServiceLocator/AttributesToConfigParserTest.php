<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\ServiceLocator;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ArgumentType;
use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\DependencyInjection\ServiceLocator\AttributesToConfigParser;
use Kaa\Component\DependencyInjection\ServiceLocator\ConfigServiceLocator;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored\IgnoredService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\JustService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Scanned\ScannedService;
use Kaa\Component\DependencyInjection\Test\ClassFixture\TestFactoryService;

beforeEach(function () {
    $this->serviceCollection = new ServiceCollection();
});

test('Ignores ignored', function () {
    $config = (new AttributesToConfigParser([
        'scan' => [
            '\\Kaa\\Component\\DependencyInjection\\Test\\ClassFixture',
        ],
        'ignore' => [
            JustService::class,
            'Kaa\\Component\\DependencyInjection\\Test\\ClassFixture\\Ignored',
        ],
    ]))->getConfig();

    $serviceLocator = new ConfigServiceLocator(
        $config,
        $this->serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();
    expect($this->serviceCollection->has(ScannedService::class))->toBe(true)
        ->and($this->serviceCollection->has(JustService::class))->toBe(false)
        ->and($this->serviceCollection->has(IgnoredService::class))->toBe(false);
});

test('attribute parsing', function () {
    $config = (new AttributesToConfigParser([
        'scan' => [
            '\\Kaa\\Component\\DependencyInjection\\Test\\ClassFixture',
        ],
        'ignore' => [
            'Kaa\\Component\\DependencyInjection\\Test\\ClassFixture\\Ignored',
        ],
    ]))->getConfig();

    $serviceLocator = new ConfigServiceLocator(
        $config,
        $this->serviceCollection,
        new ParameterCollection(),
        new AliasCollection()
    );
    $serviceLocator->locate();

    expect($this->serviceCollection->has(ScannedService::class))->toBe(true);
    $scannedService = $this->serviceCollection->get(ScannedService::class);

    expect($scannedService->constructionType)->toBe(ConstructionType::Constructor)
        ->and($scannedService->arguments[0]->type)->toBe(ArgumentType::Service)
        ->and($scannedService->arguments[0]->name)->toBe('app.service')
        ->and($scannedService->arguments[1]->type)->toBe(ArgumentType::Parameter)
        ->and($scannedService->arguments[1]->name)->toBe('app.parameter')
        ->and($this->serviceCollection->has(JustService::class))->toBe(true);

    $justService = $this->serviceCollection->get(JustService::class);

    expect($justService->constructionType)->toBe(ConstructionType::Factory)
        ->and($justService->factory->serviceName)->toBe(TestFactoryService::class)
        ->and($justService->factory->isStatic)->toBe(false)
        ->and($justService->factory->method)->toBe('invoke');
});

it('will throw if redefine alias', function () {
    $config = (new AttributesToConfigParser([
        'scan' => [
            'Kaa\\Component\\DependencyInjection\\Test\\ClassFixture\\Ignored',
        ],
    ]))->getConfig();
})->expectException(InvalidServiceDefinitionException::class);

it('will throw if use param and service in autowire together', function () {
    $config = (new AttributesToConfigParser([
        'scan' => [
            'Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored',
        ],
    ]))->getConfig();
})->expectException(InvalidServiceDefinitionException::class);
