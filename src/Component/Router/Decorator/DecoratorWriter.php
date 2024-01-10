<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\Router\Exception\DecoratorException;
use Kaa\Util\Exception\BadParameterTypeException;
use Kaa\Util\Reflection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
class DecoratorWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    /** @var DecoratedMethod[] */
    public array $decoratedMethods = [];

    public function __construct(
        private readonly SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'Router',
            className: 'Decorator',
        );

        $this->twig = TwigFactory::create(__DIR__ . '/templates');
    }

    /**
     * @return array{string, string} [Имя класса, название метода]
     */
    public function addMethod(string $className, string $serviceName, string $methodName): array
    {
        $decoratedMethodName = str_replace(['\\', '.'], '_', $serviceName) . '__' . $methodName;

        $this->decoratedMethods[] = new DecoratedMethod(
            class: $className,
            service: $serviceName,
            method: $methodName,
            decoratedMethodName: $decoratedMethodName,
        );

        return ['\Kaa\Generated\Router\Decorator', $decoratedMethodName];
    }

    /**
     * @throws BadParameterTypeException|DecoratorException|LoaderError|ReflectionException|WriterException|RuntimeError|SyntaxError
     */
    public function write(): void
    {
        foreach ($this->decoratedMethods as $decoratedMethod) {
            $this->classWriter->addMethod(
                visibility: Visibility::Public,
                name: $decoratedMethod->decoratedMethodName,
                returnType: Response::class,
                code: $this->generateCode($decoratedMethod),
                parameters: [
                    new Parameter(type: Request::class, name: 'request'),
                ],
            );
        }

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws RuntimeError|LoaderError|DecoratorException|BadParameterTypeException|SyntaxError|ReflectionException
     */
    public function generateCode(DecoratedMethod $decoratedMethod): string
    {
        $reflectionClass = new ReflectionClass($decoratedMethod->class);
        $method = $reflectionClass->getMethod($decoratedMethod->method);
        [$preDecorators, $postDecorators] = $this->getDecorators($method);

        $variables = new Variables();
        $variables->addVariable(Request::class, 'request');

        $code = [];
        foreach ($preDecorators as $decoratorAndParameter) {
            $code[] = $decoratorAndParameter->decorator->decorate(
                $method,
                $decoratorAndParameter->parameter,
                $variables,
                $this->config->newInstanceGenerator,
            );
        }

        $parameters = $this->getControllerParameterNames($method, $variables);
        $code[] = $this->twig->render('call_controller_method.php.twig', [
            'service' => $this->config->newInstanceGenerator->generate(
                $decoratedMethod->service,
                $decoratedMethod->class
            ),
            'method' => $decoratedMethod->method,
            'parameters' => $parameters,
            'retValName' => $variables->getActualReturnValueName(),
        ]);

        $variables->addVariable(
            Reflection::namedType($method->getReturnType())->getName(),
            $variables->getActualReturnValueName()
        );

        foreach ($postDecorators as $decoratorAndParameter) {
            $code[] = $decoratorAndParameter->decorator->decorate(
                $method,
                $decoratorAndParameter->parameter,
                $variables,
                $this->config->newInstanceGenerator,
            );
        }

        $code[] = $this->twig->render('return.php.twig', [
            'retValName' => $variables->getActualReturnValueName(),
        ]);

        return implode("\n\n", $code);
    }

    /**
     * @return array{DecoratorAndParameter[], DecoratorAndParameter[]} [Пре-декораторы, Пост-декораторы]
     */
    public function getDecorators(ReflectionMethod $method): array
    {
        $decoratorAttributes = $method->getAttributes(
            DecoratorInterface::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        $decorators = array_map(
            static fn (ReflectionAttribute $a) => new DecoratorAndParameter($a->newInstance(), null),
            $decoratorAttributes,
        );

        foreach ($method->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(
                DecoratorInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            foreach ($attributes as $attribute) {
                $decorators[] = new DecoratorAndParameter($attribute->newInstance(), $parameter);
            }
        }

        usort(
            $decorators,
            static fn (DecoratorAndParameter $left, DecoratorAndParameter $right) => -(
                $left->decorator->getPriority() <=> $right->decorator->getPriority()
            ),
        );

        $preDecorators = array_filter(
            $decorators,
            static fn (DecoratorAndParameter $d) => $d->decorator->getType() === DecoratorType::Pre,
        );

        $postDecorators = array_filter(
            $decorators,
            static fn (DecoratorAndParameter $d) => $d->decorator->getType() === DecoratorType::Post,
        );

        return [$preDecorators, $postDecorators];
    }

    /**
     * @return string[]
     * @throws BadParameterTypeException|DecoratorException
     */
    private function getControllerParameterNames(ReflectionMethod $method, Variables $variables): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $type = Reflection::namedType($parameter->getType())->getName();

            if ($variables->hasSame($type, $parameter->getName())) {
                $parameters[] = $parameter->getName();
                continue;
            }

            $name = $variables->getLastByType($type);
            if ($name !== null) {
                $parameters[] = $name;
                continue;
            }

            throw new DecoratorException(
                "None of the decorators generated variable for parameter {$type} \${$parameter->getName()}"
            );
        }

        return $parameters;
    }
}
