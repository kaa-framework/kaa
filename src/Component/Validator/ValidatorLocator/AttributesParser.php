<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\ValidatorLocator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

#[PhpOnly]
readonly class AttributesParser
{
    public function __construct(
        /** @var ReflectionClass[] */
        private array $validatedClasses,
    ) {
    }

    /**
     * @return array<class-string<object>, array<int, array<string, array{attribute: AssertInterface, reflectionProperty: ReflectionProperty}>>>
     */
    public function parseAttributes(): array
    {
        $attributes = [];
        foreach ($this->validatedClasses as $class) {
            $reflectionProperties = $class->getProperties();
            foreach ($reflectionProperties as $reflectionProperty) {
                $assertAttributes = $reflectionProperty->getAttributes(
                    AssertInterface::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                );

                foreach ($assertAttributes as $assertAttribute) {
                    $attribute = $assertAttribute->newInstance();
                    $attributes[$class->getName()][] = [
                        'attribute' => $attribute,
                        'reflectionProperty' => $reflectionProperty,
                    ];
                }
            }
        }

        return $attributes;
    }
}
