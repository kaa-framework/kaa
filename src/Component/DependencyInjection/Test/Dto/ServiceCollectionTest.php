<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\Dto;

use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\Service;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\ServiceAlreadyExistsException;
use Kaa\Component\DependencyInjection\Exception\ServiceDoesNotExistException;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored\IgnoredService;
use ReflectionClass;

beforeEach(function () {
    $this->serviceCollection = new ServiceCollection();
});

test('normal adding', function () {
    $this->serviceCollection->add(new Service(
        'test',
        new ReflectionClass(IgnoredService::class),
        true,
        ConstructionType::Constructor
    ));
    expect($this->serviceCollection->has('test'))->toBe(true)
        ->and($this->serviceCollection->get('test'))->toBeInstanceOf(Service::class);
    $services = $this->serviceCollection->getClassesToServices();
    expect($services[IgnoredService::class][0]->name)->toBe('test')
        ->and($services[IgnoredService::class][0]->isSingleton)->toBe(true)
        ->and($services[IgnoredService::class][0]->constructionType)->toBe(ConstructionType::Constructor);
});

it('throws error when adding exists', function () {
    $this->serviceCollection->add(new Service(
        'test',
        new ReflectionClass(IgnoredService::class),
        true,
        ConstructionType::Constructor
    ));
    $this->serviceCollection->add(new Service(
        'test',
        new ReflectionClass(IgnoredService::class),
        true,
        ConstructionType::Constructor
    ));
})->expectException(ServiceAlreadyExistsException::class);

it('throws error when trying to gen non-exists', function () {
    $this->serviceCollection->get('nox-exists');
})->expectException(ServiceDoesNotExistException::class);
