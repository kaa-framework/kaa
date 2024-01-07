<?php

namespace Kaa\Component\RequestMapperDecorator;

use Attribute;
use Kaa\Component\GeneratorContract\NewInstanceGeneratorInterface;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\RequestMapperDecorator\Exception\DecoratorException;
use Kaa\Component\Router\Decorator\DecoratorInterface;
use Kaa\Component\Router\Decorator\DecoratorType;
use Kaa\Component\Router\Decorator\Variables;
use Kaa\Component\Validator\ValidatorInterface;
use Kaa\Util\Exception\BadParameterTypeException;
use Kaa\Util\Reflection;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
readonly class MapJsonPayload implements DecoratorInterface
{
    public function __construct(
        private bool $validate = true,
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
     * @throws ReflectionException|BadParameterTypeException|DecoratorException
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

        $modelName = $parameter->getName();
        $modelClass = new ReflectionClass(Reflection::namedType($parameter->getType())->getName());

        $code = sprintf(
            "$%s = \JsonEncoder::decode($%s->getContent(), \%s::class);",
            $modelName,
            $requestVarName,
            $modelClass->name,
        );

        $variables->addVariable($modelClass->name, $modelName);

        if (class_exists(ValidatorInterface::class) && $this->validate) {
            $validatorService = $newInstanceGenerator->generate(ValidatorInterface::class, ValidatorInterface::class);

            $violationListName = 'kaaDecoratorViolationList' . $modelName;
            $code .= sprintf(
                '$%s = (%s)->validate($%s);',
                $violationListName,
                $validatorService,
                $modelName,
            );

            $code .= "\n";
            $code .= sprintf('if ($%s !== []) {', $violationListName);
            $code .= "\n";
            $code .= sprintf(
                'throw new \Kaa\Component\RequestMapperDecorator\Exception\ValidationException($%s);',
                $violationListName,
            );
            $code .= "\n}";

            $variables->addVariable('array', $violationListName);
        }

        return $code;
    }
}
