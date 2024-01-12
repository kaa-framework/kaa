<?php

namespace Kaa\Component\SecurityDecorator;

use Attribute;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\Reflection;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use Kaa\Component\Security\SecurityInterface;
use Kaa\Component\SecurityDecorator\Exception\DecoratorException;
use ReflectionMethod;
use ReflectionParameter;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
class CurrentUser implements DecoratorInterface
{
    public function getType(): DecoratorType
    {
        return DecoratorType::Pre;
    }

    public function getPriority(): int
    {
        return 100;
    }

    /**
     * @throws DecoratorException|BadTypeException
     */
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

        $type = Reflection::namedType($parameter->getType())->getName();
        $variables->addVariable($type, $parameter->getName());

        $service = $newInstanceGenerator->generate(
            SecurityInterface::class,
            'Kaa\Generated\Security\Security',
        );

        return sprintf(
            '$%s = instance_cast((%s)->getUser(), \%s::class);',
            $parameter->getName(),
            $service,
            $type,
        );
    }
}
