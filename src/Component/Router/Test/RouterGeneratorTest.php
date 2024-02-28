<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test;

use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\Router\RouterGenerator;
use Kaa\Generated\Router\Decorator;
use Kaa\Generated\Router\Router;

beforeAll(function () {
    $sharedConfig = new SharedConfig(dirname(__DIR__, 4) . '/generated');
    $config = [
        'scan' => [
            'Kaa\Component\Router\Test\Handlers\Integrative',
        ],
    ];
    (new RouterGenerator())->generate($sharedConfig, $config);
});

beforeEach(function () {
    $this->router = new Router();
});

test('empty handling', function () {
    $request = Request::create('');
    [$obj, $method] = $this->router->findAction($request);
    expect($obj)->toBe(null);
});

test('correct GET handling', function () {
    $request = Request::create('/integration/test');
    [$obj, $method] = $this->router->findAction($request);
    $response = [$obj, $method]($request);
    expect($obj)->toBeInstanceOf(Decorator::class)
        ->and($method)->toBe('Kaa_Component_Router_Test_Handlers_Integrative_IntegrativeHandler__first')
        ->and($response->getStatusCode())->toBe(200);
});

test('correct PUT handling', function () {
    $request = Request::create('/integration/test/123', 'PUT');
    [$obj, $method] = $this->router->findAction($request);
    $response = [$obj, $method]($request);
    expect($obj)->toBeInstanceOf(Decorator::class)
        ->and($method)->toBe('Kaa_Component_Router_Test_Handlers_Integrative_IntegrativeHandler__second')
        ->and($request->attributes->get('id'))->toBe('123')
        ->and($response->getStatusCode())->toBe(200);
});

test('correct HEAD handling', function () {
    $request = Request::create('/integration/test/4232/22', 'HEAD');
    [$obj, $method] = $this->router->findAction($request);
    $response = [$obj, $method]($request);
    expect($obj)->toBeInstanceOf(Decorator::class)
        ->and($method)->toBe('Kaa_Component_Router_Test_Handlers_Integrative_IntegrativeHandler__fourth')
        ->and($request->attributes->get('id'))->toBe('4232')
        ->and($request->attributes->get('num'))->toBe('22')
        ->and($response->getStatusCode())->toBe(200);
});

test('correct POST handling', function () {
    $request = Request::create('/integration/10/max', 'POST');
    [$obj, $method] = $this->router->findAction($request);
    $response = [$obj, $method]($request);
    expect($obj)->toBeInstanceOf(Decorator::class)
        ->and($method)->toBe('Kaa_Component_Router_Test_Handlers_Integrative_IntegrativeHandler__fifth')
        ->and($request->attributes->get('id'))->toBe('10')
        ->and($response->getStatusCode())->toBe(200);
});

test('correct small POST handling', function () {
    $request = Request::create('/integration', 'POST');
    [$obj, $method] = $this->router->findAction($request);
    $response = [$obj, $method]($request);
    expect($obj)->toBeInstanceOf(Decorator::class)
        ->and($method)->toBe('Kaa_Component_Router_Test_Handlers_Integrative_IntegrativeHandler__sixth')
        ->and($response->getStatusCode())->toBe(200);
});

test('correct big POST handling', function () {
    $request = Request::create('/integration/test/7/current', 'POST');
    [$obj, $method] = $this->router->findAction($request);
    $response = [$obj, $method]($request);
    expect($obj)->toBeInstanceOf(Decorator::class)
        ->and($method)->toBe('Kaa_Component_Router_Test_Handlers_Integrative_IntegrativeHandler__third')
        ->and($request->attributes->get('id'))->toBe('7')
        ->and($response->getStatusCode())->toBe(200);
});
