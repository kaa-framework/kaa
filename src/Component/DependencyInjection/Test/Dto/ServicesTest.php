<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\Dto;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\Service;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Dto\Services;
use Kaa\Component\DependencyInjection\Exception\ServiceDoesNotExistException;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored\IgnoredService;
use ReflectionClass;

beforeEach(function () {
    $this->serviceCollection = new ServiceCollection();
    $this->aliasCollection = new AliasCollection();
});

test('normal getting class', function () {
    $this->serviceCollection->add(new Service(
        'test',
        new ReflectionClass(IgnoredService::class),
        true,
        ConstructionType::Constructor
    ));
    $this->aliasCollection->addAlias('test', 'alias.test');
    $services = new Services($this->serviceCollection, $this->aliasCollection);
    expect($services->getClass('test'))->toBe(IgnoredService::class)
        ->and($services->getClass('alias.test'))->toBe(IgnoredService::class);
});

it('throws error when trying to find non-exists', function () {
    $services = new Services($this->serviceCollection, $this->aliasCollection);
    $services->getClass('non-exists');
})->expectException(ServiceDoesNotExistException::class);
