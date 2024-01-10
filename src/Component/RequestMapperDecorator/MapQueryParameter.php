<?php

namespace Kaa\Component\RequestMapperDecorator;

use Attribute;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\RequestMapperDecorator\Exception\DecoratorException;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use Kaa\Util\Exception\BadParameterTypeException;
use Kaa\Util\Reflection;
use ReflectionMethod;
use ReflectionParameter;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
readonly class MapQueryParameter implements DecoratorInterface
{
    public function __construct(
        private ?string $name = null,
    ) {
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getType(): DecoratorType
    {
        return DecoratorType::Pre;
    }

    /**
     * @throws BadParameterTypeException|DecoratorException
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

        $requestVarName = $variables->getLastByType(Request::class) ?? throw new DecoratorException(
            sprintf(
                'No variable with type %s is available for decorator %s',
                Request::class,
                static::class,
            )
        );

        $variables->addVariable(Reflection::namedType($parameter->getType())->getName(), $parameter->getName());

        return sprintf(
            '$%s = (%s) %s->query->get(%s)',
            $parameter->getName(),
            Reflection::namedType($parameter->getType())->getName(),
            $requestVarName,
            $this->name ?? $parameter->getName(),
        );
    }
}
