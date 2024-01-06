<?php

namespace Kaa\Component\Router\Decorator;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\Router\Exception\DecoratorException;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Util\Exception\BadParameterTypeException;
use Kaa\Util\Reflection;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
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
        $variables->addVariable(Request::class, 'kaaRequest');

        $code = [];
        foreach ($preDecorators as $decorator) {
            $code[] = $decorator->decorate($method, $variables, $this->config->newInstanceGenerator);
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

        $code = [];
        foreach ($postDecorators as $decorator) {
            $code[] = $decorator->decorate($method, $variables, $this->config->newInstanceGenerator);
        }

        $code[] = $this->twig->render('return.php.twig', [
            'retValName' => $variables->getActualReturnValueName(),
        ]);

        return implode("\n\n", $code);
    }

    /**
     * @return array{DecoratorInterface[], DecoratorInterface[]} [Пре-декораторы, Пост-декораторы]
     */
    public function getDecorators(ReflectionMethod $method): array
    {
        $decoratorAttributes = $method->getAttributes(
            DecoratorInterface::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        $decorators = array_map(
            static fn (ReflectionAttribute $a) => $a->newInstance(),
            $decoratorAttributes,
        );

        usort(
            $decorators,
            static fn (DecoratorInterface $left, DecoratorInterface $right) => -($left->getPriority(
            ) <=> $right->getPriority()),
        );

        $preDecorators = array_filter(
            $decorators,
            static fn (DecoratorInterface $d) => $d->getType() === DecoratorType::Pre,
        );

        $postDecorators = array_filter(
            $decorators,
            static fn (DecoratorInterface $d) => $d->getType() === DecoratorType::Pre,
        );

        return [$preDecorators, $postDecorators];
    }

    /**
     * @throws RouterGeneratorException
     */
    private function writeFile(): void
    {
        $directory = $this->config->exportDirectory . '/Router';
        if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
            throw new RouterGeneratorException("Directory {$directory} was not created");
        }

        file_put_contents(
            $directory . '/Decorator.php',
            (new PsrPrinter())->printFile($this->file),
        );
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
