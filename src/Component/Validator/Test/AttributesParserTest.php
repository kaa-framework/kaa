<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test;

use Exception;
use Kaa\Component\Validator\Assert\Blank;
use Kaa\Component\Validator\Assert\NotBlank;
use Kaa\Component\Validator\Test\Models\TestModel;
use Kaa\Component\Validator\ValidatorLocator\AttributesParser;
use Kaa\Component\Validator\ValidatorLocator\ModelLocator;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class AttributesParserTest extends TestCase
{
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->testModel = new TestModel();
        $this->config = [
            'scan' => [
                'Kaa\Component\Validator\Test\Models\Entity',
                'Kaa\Component\Validator\Test\Models\SomeModel',
            ],
        ];
        $this->validatedClasses = (new ModelLocator($this->config))->locate();
        $this->assertNotBlank = new NotBlank();
        $this->assertBlank = new Blank();
    }

    public function testCountClasses(): void
    {
        $attributes = (new AttributesParser($this->validatedClasses))->parseAttributes();
        assertEquals(1, count($attributes));
    }

    public function testCountAttributes(): void
    {
        $attributes = (new AttributesParser($this->validatedClasses))->parseAttributes();
        assertEquals(1, count($attributes['Kaa\Component\Validator\Test\Models\SomeModel']));
    }

    public function testAttributes(): void
    {
        $attributes = (new AttributesParser($this->validatedClasses))->parseAttributes();
        assertEquals($this->assertBlank, $attributes['Kaa\Component\Validator\Test\Models\SomeModel'][0]['attribute']);
    }
}
