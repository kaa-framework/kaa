<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Kaa\Component\Validator\Assert\Blank;
use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorLocator\AttributesParser;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;

beforeEach(function () {
    $this->testModel = new TestModel();
    $this->config = [
        'scan' => [
            'Kaa\Component\Validator\Test\Models\Entity',
            'Kaa\Component\Validator\Test\Models\SomeModel',
        ],
    ];
    $this->assertBlank = new Blank();
    $this->validatedClasses = (new ModelLocator($this->config))->locate();
    $this->attributes = (new AttributesParser($this->validatedClasses))->parseAttributes();
});

test('count classes', function () {
    expect($this->attributes)
        ->toHaveCount(1);
});

test('count attributes', function () {
    expect($this->attributes['Kaa\Component\Validator\Test\Models\SomeModel'])
        ->toHaveCount(1);
});

test('correct attribute', function () {
    expect($this->attributes['Kaa\Component\Validator\Test\Models\SomeModel'][0]['attribute'])
        ->toEqual($this->assertBlank);
});
