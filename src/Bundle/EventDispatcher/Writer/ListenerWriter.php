<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher\Writer;

use Kaa\Bundle\EventDispatcher\Util\ListenerMethodName;
use Kaa\Component\EventDispatcher\EventInterface;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Util\Reflection;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use ReflectionClass;
use ReflectionException;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ListenerWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    /**
     * @param mixed[] $listeners
     */
    public function __construct(
        private SharedConfig $config,
        private array $listeners,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'EventDispatcher',
            className: 'Listener',
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws WriterException|SyntaxError|ReflectionException|RuntimeError|LoaderError|BadTypeException
     */
    public function write(): void
    {
        foreach ($this->listeners as $listener) {
            $this->addMethod($listener);
        }

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @param mixed[] $listener
     * @throws ReflectionException|LoaderError|RuntimeError|SyntaxError|BadTypeException
     */
    private function addMethod(array $listener): void
    {
        $listenerClass = (new ReflectionClass($listener['service_class'] ?? $listener['service']));
        $eventType = $listenerClass->getMethod($listener['method'])->getParameters()[0]->getType();

        $code = $this->twig->render('listener_method.php.twig', [
            'service' => $this->config->newInstanceGenerator->generate($listener['service'], $listenerClass->getName()),
            'method' => $listener['method'],
            'type' => Reflection::namedType($eventType)->getName(),
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: ListenerMethodName::name($listener),
            returnType: 'void',
            code: $code,
            parameters: [
                new Parameter(type: EventInterface::class, name: 'event'),
            ],
            comment: '@kphp-required',
        );
    }
}
