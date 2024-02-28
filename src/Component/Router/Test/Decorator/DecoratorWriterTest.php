<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Decorator;

use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Router\Decorator\DecoratorWriter;
use Kaa\Component\Router\Exception\DecoratorException;

beforeEach(function () {
    $generatorsConfig = require dirname(__DIR__) . '/config/bundles.php';
    $newInstanceGenerator = new $generatorsConfig['instanceGenerator']();
    $sharedConfig = new SharedConfig(dirname(__DIR__, 5) . '/generated', $newInstanceGenerator);
    $this->decoratorWriter = new DecoratorWriter($sharedConfig);
});

it('writes to file', function () {
    $this->decoratorWriter->addMethod(
        'Kaa\Component\Router\Test\Handlers\Scan\ScanedHandler',
        'TestService',
        'healthcheck'
    );
    $this->decoratorWriter->write();
    expect(class_exists('Kaa\Component\Router\Test\Handlers\Scan\ScanedHandler'))->toBe(true);
});

it('throws error', function () {
    $this->decoratorWriter->addMethod(
        'Kaa\Component\Router\Test\Handlers\Ignore\ThrowHandler',
        'TestService',
        'nothing'
    );
    $this->decoratorWriter->write();
})->expectException(DecoratorException::class);
