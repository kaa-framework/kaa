<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Exception;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Validator\Test\Models\SomeModel;
use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorGenerator;
use Kaa\Generated\Validator\Validator;

trait SwitchCaseTest
{
    /**
     * @throws Exception
     */
    private function generateValidator(): void
    {
        $this->testModel = new TestModel();
        $this->sharedConfig = new SharedConfig('../generated');
        $this->config = [
            'scan' => [
                'Kaa\Component\Validator\Test\Models\SomeModel',
            ],
        ];
        $validatorGenerator = new ValidatorGenerator();
        $validatorGenerator->generate($this->sharedConfig, $this->config);
    }
}

uses(SwitchCaseTest::class);

beforeEach(function () {
    $this->generateValidator();
    $this->validator = new Validator();
    $this->model = new SomeModel();
    $this->violationsList = $this->validator->validate($this->model);
});

test('switch case', function () {
    expect($this->violationsList)
        ->toHaveCount(1)
        ->and($this->violationsList[0]->getMessage())
        ->toBe('This value should be blank.');
});
