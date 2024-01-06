<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Exception;
use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotContains;

class ModelLocatorTest extends TestCase
{
    protected function setUp(): void
    {
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
    }

    /**
     * @throws Exception
     */
    public function testContainsModel(): void
    {
        $validatedClasses = (new ModelLocator($this->config))->locate();
        assertEquals($this->reflectionClassTestModel, $validatedClasses[0]);
        assertEquals($this->reflectionClassSomeModel, $validatedClasses[1]);
    }

    /**
     * @throws Exception
     */
    public function testNotContainsModel(): void
    {
        $validatedClasses = (new ModelLocator($this->config))->locate();
        assertNotContains($this->reflectionClassEntity, $validatedClasses);
    }
}
