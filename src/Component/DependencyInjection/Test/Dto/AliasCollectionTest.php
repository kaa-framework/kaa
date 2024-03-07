<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Test\Dto;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;

beforeEach(function () {
    $this->aliasCollection = new AliasCollection();
});

test('normal adding alias', function () {
    $this->aliasCollection->addAlias('testService', 'alias.test');
    expect($this->aliasCollection->getServiceName('alias.test'))->toBe('testService');
});

test('getting service aliases', function () {
    $this->aliasCollection->addAlias('testService', 'alias.test');
    $this->aliasCollection->addAlias('testService', 'another.alias');
    $aliases = $this->aliasCollection->getServiceAliases('testService');
    expect(in_array('alias.test', $aliases))->toBe(true)
        ->and(in_array('another.alias', $aliases))->toBe(true);
});

test('adding empty string as service', function () {
    $this->aliasCollection->addAlias('', '');
    expect($this->aliasCollection->getServiceName(''))->toBe('');
});

test('getting non-existent service', function () {
    expect($this->aliasCollection->getServiceName('not.exists.alias'))->toBe(null);
});
