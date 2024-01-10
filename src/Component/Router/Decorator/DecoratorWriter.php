<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\Router\Exception\DecoratorException;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Tmp\KaaPrinter;
use Kaa\Util\Exception\BadParameterTypeException;
use Kaa\Util\Reflection;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
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
    /** @var DecoratedMethod[] */
    public array $decoratedMethods = [];

    private PhpFile $file;
    private ClassType $class;
    private Twig\Environment $twig;

    public function __construct(
        private readonly SharedConfig $config,
    ) {
        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\Router');
        $this->class = $namespace->addClass('Decorator');

        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

        return new Twig\Environment($loader);
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
     * @throws BadParameterTypeException|DecoratorException|LoaderError|ReflectionException|RouterGeneratorException|RuntimeError|SyntaxError
     */
    public function write(): void
    {
        foreach ($this->decoratedMethods as $decoratedMethod) {
            $this->decorate($decoratedMethod);
        }

        $this->writeFile();
    }

    /**
     * @throws BadParameterTypeException|DecoratorException|ReflectionException|LoaderError|RuntimeError|SyntaxError
     */
    private function decorate(DecoratedMethod $decoratedMethod): void
    {
        $method = $this->class->addMethod($decoratedMethod->decoratedMethodName);
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setReturnType(Response::class);
        $method->setBody($this->generateCode($decoratedMethod));

        $parameter = $method->addParameter('request');
        $parameter->setType(Request::class);
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

    /**
     * @throws RouterGeneratorException
     */
    private function writeFile(): void
    {
        $directory = $this->config->exportDirectory . '/Router';
        if (!is_dir($directory) && !mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new RouterGeneratorException("Directory {$directory} was not created");
        }

        file_put_contents(
            $directory . '/Decorator.php',
            (new KaaPrinter())->printFile($this->file),
        );
    }
}
