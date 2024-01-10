<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher\Writer;

use Kaa\Bundle\EventDispatcher\ListenerMethodName;
use Kaa\Component\EventDispatcher\EventInterface;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Tmp\KaaPrinter;
use Kaa\Util\Exception\BadParameterTypeException;
use Kaa\Util\Reflection;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ListenerWriter
{
    private PhpFile $file;
    private ClassType $class;
    private Twig\Environment $twig;

    /**
     * @param mixed[] $listeners
     */
    public function __construct(
        private SharedConfig $config,
        private array $listeners,
    ) {
        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\EventDispatcher');
        $this->class = $namespace->addClass('Listener');

        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

        return new Twig\Environment($loader);
    }

    /**
     * @throws RouterGeneratorException|SyntaxError|ReflectionException|RuntimeError|LoaderError
     */
    public function write(): void
    {
        foreach ($this->listeners as $listener) {
            $this->addMethod($listener);
        }

        $this->writeFile();
    }

    /**
     * @param mixed[] $listener
     * @throws ReflectionException|LoaderError|RuntimeError|SyntaxError|BadParameterTypeException
     */
    private function addMethod(array $listener): void
    {
        $listenerClass = (new ReflectionClass($listener['service_class'] ?? $listener['service']));
        /** @var ReflectionNamedType $eventType */
        $eventType = $listenerClass->getMethod($listener['method'])->getParameters()[0]->getType();

        $code = $this->twig->render('listener_method.php.twig', [
            'service' => $this->config->newInstanceGenerator->generate($listener['service'], $listenerClass->getName()),
            'method' => $listener['method'],
            'type' => Reflection::namedType($eventType)->getName(),
        ]);

        $method = $this->class->addMethod(ListenerMethodName::get($listener));

        $method->setReturnType('void');
        $method->setComment('@kphp-required');
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setBody($code);

        $parameter = $method->addParameter('event');
        $parameter->setType(EventInterface::class);
    }

    /**
     * @throws RouterGeneratorException
     */
    private function writeFile(): void
    {
        $directory = $this->config->exportDirectory . '/EventDispatcher';
        if (!is_dir($directory) && !mkdir($directory, recursive: true) && !is_dir($directory)) {
            throw new RouterGeneratorException("Directory {$directory} was not created");
        }

        file_put_contents(
            $directory . '/Listener.php',
            (new KaaPrinter())->printFile($this->file),
        );
    }
}
