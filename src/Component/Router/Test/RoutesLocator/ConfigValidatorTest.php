<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\RoutesLocator;

use Kaa\Component\Router\Exception\ValidationException;
use Kaa\Component\Router\Router\RouteLocator\ConfigValidator;

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
});

it('no changed', function () {
    $old = $this->config;
    ConfigValidator::validate($this->config);
    $this->assertSame($old, $this->config);
});

it('empty ', function () {
    $config = [];
    ConfigValidator::validate($config);
    $this->assertSame([], $config);
});

it('throws exception', function () {
    $this->config['routes'][] = [
        'route' => '/external-api',
        'method' => 'GET',
        'service' => 'TestHandler',
        'classMethod' => 'callExternalApi',
    ];
    ConfigValidator::validate($this->config);
})->throws(ValidationException::class);

it('throws exception but differ method', function () {
    $this->config['routes'][] = [
        'route' => '/external-api',
        'method' => 'GET',
        'service' => 'ThrowHandler',
        'classMethod' => 'error',
    ];
    ConfigValidator::validate($this->config);
})->throws(ValidationException::class);

it('normal works', function () {
    ConfigValidator::validate($this->config);
})->throwsNoExceptions();
