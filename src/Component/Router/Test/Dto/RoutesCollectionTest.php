<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Dto;

use Kaa\Component\Generator\DefaultNewInstanceGenerator;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Router\Decorator\DecoratorWriter;
use Kaa\Component\Router\Router\Dto\RouteDto;
use Kaa\Component\Router\Router\Dto\RoutesCollection;

beforeEach(function () {
    $this->config = [
        'scan' => [
            '\\Kaa\\Component\\Router\\Test\\Handlers\\Scan\\',
        ],
        'routes' => [
            [
                'route' => '/external-api',
                'method' => 'GET',
                'service' => 'TestHandler',
                'classMethod' => 'callExternalApi',
            ],
            [
                'route' => '/api/get',
                'method' => 'POST',
                'service' => ['class' => 'TestHandler',
                    'name' => 'app.service'],
                'classMethod' => 'callExternalApi',
            ],
        ],
    ];
    $newInstanceGenerator = new DefaultNewInstanceGenerator();
    $sharedConfig = new SharedConfig(dirname(__DIR__) . '/generated', $newInstanceGenerator);
    $this->decoratorWriter = new DecoratorWriter($sharedConfig);
    $this->routesCollection = new RoutesCollection($this->config, $this->decoratorWriter);
});

it('doesnt find empty path', function () {
    expect($this->routesCollection->has(''))->toBe(false);
});

it('returns that path exists', function () {
    expect($this->routesCollection->has('/external-api'))->toBe(true)
        ->and($this->routesCollection->has('/api/get'))->toBe(true);
});

it('returns that path doesnt exists', function () {
    $routesCollection = new RoutesCollection($this->config, $this->decoratorWriter);
    expect($routesCollection->has('/test'))->toBe(false);
});

it('search path with method', function () {
    expect($this->routesCollection->has('/api/get', 'POST'))->toBe(true)
        ->and($this->routesCollection->has('/external-api', 'GET'))->toBe(true);
});

it('cant find that path with method', function () {
    expect($this->routesCollection->has('/external-api', 'POST'))->toBe(false)
        ->and($this->routesCollection->has('/api/get', 'PUT'))->toBe(false);
});

it('is used as iterator without exception', function () {
    foreach ($this->routesCollection as $router) {
        expect($router instanceof RouteDto)->toBe(true);
    }
});
