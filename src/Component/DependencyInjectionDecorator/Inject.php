<?php

namespace Kaa\Component\DependencyInjectionDecorator;

use Attribute;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\Reflection;
use Kaa\Component\RequestMapperDecorator\Exception\DecoratorException;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use ReflectionMethod;
use ReflectionParameter;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
readonly class Inject implements DecoratorInterface
{
    public function __construct(
        private ?string $serviceName = null,
    ) {
    }

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

        $parameterType = Reflection::namedType($parameter->getType())->getName();
        $variables->addVariable($parameterType, $parameter->name);

        $serviceCode = $newInstanceGenerator->generate($this->serviceName ?? $parameterType, $parameterType);

        return sprintf('$%s = %s;', $parameter->name, $serviceCode);
    }
}
