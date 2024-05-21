<?php

namespace Kaa\Component\RequestMapperDecorator;

use Attribute;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\RequestMapperDecorator\Exception\DecoratorException;
use Kaa\Component\Router\Decorator\Variables;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
readonly class MapRequestPayload extends AbstractBagDecorator
{
    protected function getInputBagName(): string
    {
        return 'request';
    }

    public function decorate(
        ReflectionMethod $decoratedMethod,
        ?ReflectionParameter $parameter,
        Variables $variables,
        NewInstanceGeneratorInterface $newInstanceGenerator,
    ): string {
        if ($parameter === null) {
            throw new DecoratorException(
                sprintf(
                    'Decorator %s must be used only on parameters',
                    static::class,
                )
            );
        }

        if ((!$parameter->getType()?->isBuiltin()) && ($parameter->getDeclaringClass()?->getProperties() !== null)) {
            $cls = new ReflectionClass($parameter->getType()?->getName());
            foreach ($cls->getProperties() as $property) {
                if (!$property->getType()?->isBuiltin()) {
                    throw new DecoratorException(
                        sprintf(
                            'Decorator %s must be used only for class without nesting, but %s has property %s',
                            static::class,
                            $parameter->name,
                            $property->getName()
                        )
                    );
                }
            }
        }

        return parent::decorate($decoratedMethod, $parameter, $variables, $newInstanceGenerator);
    }
}
