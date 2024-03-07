<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\Dto;

use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Exception\ParameterDoesNotExistException;
use Kaa\Component\DependencyInjection\Test\ClassFixture\Ignored\IgnoredService;

beforeEach(function () {
    $this->parameterCollection = new ParameterCollection();
});

test('normal adding', function () {
    $this->parameterCollection->set('test', 1);
    expect($this->parameterCollection->has('test'))->toBe(true)
        ->and($this->parameterCollection->get('test'))->toBe(1);
});

test('normal reset', function () {
    $this->parameterCollection->set('test', 'zero');
    expect($this->parameterCollection->get('test'))->toBe('zero');
    $this->parameterCollection->set('test', 12);
    expect($this->parameterCollection->get('test'))->toBe(12);
    $this->parameterCollection->set('test', new IgnoredService());
    expect($this->parameterCollection->get('test'))->toBeInstanceOf(IgnoredService::class);
});

test('raising exception when non-existing parameter', function () {
    $this->parameterCollection->get('non-exists');
})->expectException(ParameterDoesNotExistException::class);
