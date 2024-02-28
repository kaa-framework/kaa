<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Test\Decorator;

use Kaa\Component\Router\Decorator\Variables;

beforeEach(function () {
    $this->variables = new Variables();
});

test('controller return value name', function () {
    expect($this->variables->getControllerReturnValueName())->toBe('kaaRetVal');
});

test('getting actual return value name', function () {
    $this->variables->setActualReturnValueName('Test');
    expect($this->variables->getActualReturnValueName())->toBe('Test');
});

test('adding int', function () {
    $this->variables->addVariable('int', 'test');
    expect($this->variables)
        ->hasSame('int', 'test')->toBe(true)
        ->hasSame('test', 'int')->toBe(false)
        ->hasSame('float', 'test')->toBe(false)
        ->hasSame('string', 'test')->toBe(false);
});

test('adding float', function () {
    $this->variables->addVariable('float', 'floatVar');
    expect($this->variables)
        ->hasSame('float', 'floatVar')->toBe(true)
        ->hasSame('floatVar', 'float')->toBe(false)
        ->hasSame('int', 'floatVar')->toBe(false)
        ->hasSame('string', 'floatVar')->toBe(false);
});

test('adding string', function () {
    $this->variables->addVariable('string', 'stringVar');
    expect($this->variables)
        ->hasSame('string', 'stringVar')->toBe(true)
        ->hasSame('stringVar', 'string')->toBe(false)
        ->hasSame('float', 'stringVar')->toBe(false)
        ->hasSame('int', 'stringVar')->toBe(false);
});

test('adding several values', function () {
    $this->variables->addVariable('int', 'intVar');
    $this->variables->addVariable('float', 'floatVar');
    $this->variables->addVariable('string', 'stringVar');
    expect($this->variables)
        ->hasSame('int', 'intVar')->toBe(true)
        ->hasSame('float', 'floatVar')->toBe(true)
        ->hasSame('string', 'stringVar')->toBe(true);
});

test('getting last by type', function () {
    $this->variables->addVariable('int', 'firstVar');
    $this->variables->addVariable('float', 'secondVar');
    $this->variables->addVariable('int', 'thirdVar');
    expect($this->variables)
        ->getLastByType('int')->toBe('thirdVar')
        ->getLastByType('float')->toBe('secondVar');
});
