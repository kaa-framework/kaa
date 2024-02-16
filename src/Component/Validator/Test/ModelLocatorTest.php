<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;
use ReflectionClass;

beforeEach(function () {
    $this->testModel = new TestModel();
    $this->config = [
        'scan' => [
            'Kaa\Component\Validator\Test\Models\TestModel',
            'Kaa\Component\Validator\Test\Models\Entity',
            'Kaa\Component\Validator\Test\Models\SomeModel',
        ],
    ];
    $this->reflectionClassTestModel = new ReflectionClass('Kaa\Component\Validator\Test\Models\TestModel');
    $this->reflectionClassSomeModel = new ReflectionClass('Kaa\Component\Validator\Test\Models\SomeModel');
    $this->reflectionClassEntity = new ReflectionClass('Kaa\Component\Validator\Test\Models\Entity');
    $this->validatedClasses = (new ModelLocator($this->config))->locate();
});

test('correct contains models', function () {
    expect($this->validatedClasses)
        ->toHaveCount(2)
        ->not->toContain($this->reflectionClassEntity)
        ->and($this->validatedClasses[0])
        ->toEqual($this->reflectionClassTestModel)
        ->and($this->validatedClasses[1])
        ->toEqual($this->reflectionClassSomeModel);
});
