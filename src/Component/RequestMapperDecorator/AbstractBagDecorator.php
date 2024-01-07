<?php

namespace Kaa\Component\RequestMapperDecorator;

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
use ReflectionType;

#[PhpOnly]
abstract readonly class AbstractBagDecorator implements DecoratorInterface
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
     * @throws DecoratorException|BadParameterTypeException|ReflectionException
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
        $constructorParameters = $this->getConstructorParameters($requestVarName, $modelClass);

        $parameterCode = [];
        foreach ($modelClass->getProperties() as $property) {
            if (array_key_exists($property->getName(), $constructorParameters)) {
                continue;
            }

            $parameterCode[] = SetUtil::generateSetStatement(
                $property,
                $modelName,
                $this->generateGetFromBagCode(
                    $property->getType(),
                    $property->name,
                    $requestVarName,
                ),
            );
        }

        $variables->addVariable($modelClass->getName(), $modelName);

        $code = [
            sprintf(
                '$%s = new \%s(%s);',
                $modelName,
                $modelClass->name,
                implode(",\n", $constructorParameters)
            ),
            ...$parameterCode,
        ];

        if (class_exists(ValidatorInterface::class) && $this->validate) {
            $validatorService = $newInstanceGenerator->generate(ValidatorInterface::class, ValidatorInterface::class);

            $violationListName = 'kaaDecoratorViolationList' . $modelName;

            $code[] = "\n";
            $code[] = sprintf(
                '$%s = (%s)->validate($%s);',
                $violationListName,
                $validatorService,
                $modelName,
            );

            $code[] = "\n";
            $code[] = sprintf('if ($%s !== []) {', $violationListName);
            $code[] = "\n";
            $code[] = sprintf(
                'throw new \Kaa\Component\RequestMapperDecorator\Exception\ValidationException($%s);',
                $violationListName,
            );
            $code[] = "\n}";

            $variables->addVariable('array', $violationListName);
        }

        return implode("\n", $code);
    }

    /**
     * @return array<string, string> [Имя параметра => код для установки параметра]
     * @throws BadParameterTypeException
     */
    private function getConstructorParameters(string $requestVarName, ReflectionClass $modelClass): array
    {
        $constructor = $modelClass->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $constructorArguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $constructorArguments[$parameter->getName()] = $this->generateGetFromBagCode(
                $parameter->getType(),
                $parameter->name,
                $requestVarName,
            );
        }

        return $constructorArguments;
    }

    /**
     * @throws BadParameterTypeException
     */
    private function generateGetFromBagCode(?ReflectionType $type, string $paramName, string $requestVarName): string
    {
        return sprintf(
            '(%s) %s->%s->get(%s)',
            Reflection::namedType($type)->getName(),
            $requestVarName,
            $this->getInputBagName(),
            $paramName,
        );
    }

    abstract protected function getInputBagName(): string;
}
