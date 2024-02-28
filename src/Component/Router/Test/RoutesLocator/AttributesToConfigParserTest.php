<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\RoutesLocator;

use Kaa\Component\Generator\DefaultNewInstanceGenerator;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Router\Decorator\DecoratorWriter;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Component\Router\Router\Dto\RoutesCollection;
use Kaa\Component\Router\Router\RouteLocator\AttributesToConfigParser;

beforeEach(function () {
    $this->parserConfig = [
        'scan' => [
            '\\Kaa\\Component\\Router\\Test\\Handlers\\Scan\\',
        ],
    ];
    $newInstanceGenerator = new DefaultNewInstanceGenerator();
    $sharedConfig = new SharedConfig(dirname(__DIR__) . '/generated', $newInstanceGenerator);
    $this->decoratorWriter = new DecoratorWriter($sharedConfig);
});

it('ignores namespace', function () {
    $config = (new AttributesToConfigParser($this->parserConfig))->getConfig();
    $routeCollection = new RoutesCollection($config, $this->decoratorWriter);
    expect($routeCollection)
        ->has('/test/healthcheck', 'GET')->toBe(true)
        ->has('/test/posthealthcheck', 'POST')->toBe(true)
        ->has('/ignore')->toBe(false);
});

it('throws exception', function () {
    $this->parserConfig['scan'][] = '\\Kaa\\Component\\Router\\Test\\Handlers\\Ignore\\';
    (new AttributesToConfigParser($this->parserConfig))->getConfig();
})->expectException(RouterGeneratorException::class);
