<?php

namespace Kaa\Component\RequestMapperDecorator;

use Kaa\Component\Generator\NewInstanceGeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Writer\TwigFactory;
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
use ReflectionProperty;
use ReflectionType;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
abstract readonly class AbstractBagDecorator implements DecoratorInterface
{
    private Twig\Environment $twig;

    public function __construct(
        private bool $validate = true,
    ) {
        $this->twig = TwigFactory::create(__DIR__ . '/templates');
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
     * @throws BadParameterTypeException|DecoratorException|ReflectionException|LoaderError|RuntimeError|SyntaxError
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

        $code = [
            sprintf(
                '$%s = new \%s(%s);',
                $modelName,
                $modelClass->name,
                implode(",\n", $constructorParameters)
            ),
        ];

        foreach ($modelClass->getProperties() as $property) {
            if (array_key_exists($property->getName(), $constructorParameters)) {
                continue;
            }

            $code[] = $this->generateSetStatement(
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

        if (class_exists(ValidatorInterface::class) && $this->validate) {
            $validatorService = $newInstanceGenerator->generate(ValidatorInterface::class, ValidatorInterface::class);

            $violationListName = 'kaaDecoratorViolationList' . $modelName;
            $code[] = $this->twig->render('validate.php.twig', [
                'violationList' => $violationListName,
                'service' => $validatorService,
                'model' => $modelName,
            ]);

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

    /**
     * Генерирует строчку кода, которая устанавливает свойству $reflectionProperty объекта с именем $objectName
     * значение $value
     *
     * @param string $value Может быть строкой с константой, вызовом метода, конструктора и т.д.
     * @throws ReflectionException|DecoratorException
     */
    public function generateSetStatement(
        ReflectionProperty $reflectionProperty,
        string $modelName,
        string $value,
    ): string {
        if ($reflectionProperty->isPublic()) {
            return sprintf('$%s->%s = %s;', $modelName, $reflectionProperty->name, $value);
        }

        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $setterMethodName = $this->getMethodNameWithRightCase(
            $reflectionClass,
            'set' . $reflectionProperty->name
        );

        if ($setterMethodName === null) {
            throw new DecoratorException(
                sprintf(
                    'Property %s::%s is private and it`s class does not have setter method',
                    $reflectionClass->name,
                    $reflectionProperty->name,
                )
            );
        }

        if (!$reflectionClass->getMethod($setterMethodName)->isPublic()) {
            throw new DecoratorException(
                sprintf(
                    'Property %s::%s is private and it`s setter %s is also private',
                    $reflectionClass->name,
                    $reflectionProperty->name,
                    $reflectionProperty->name,
                )
            );
        }

        return sprintf('$%s->%s(%s);', $modelName, $setterMethodName, $value);
    }

    private function getMethodNameWithRightCase(ReflectionClass $reflectionClass, string $methodName): ?string
    {
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (strcasecmp($reflectionMethod->name, $methodName) === 0) {
                return $reflectionMethod->name;
            }
        }

        return null;
    }

    abstract protected function getInputBagName(): string;
}
