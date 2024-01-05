<?php

declare(strict_types=1);

namespace Kaa\Bundle\Router;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\HttpKernel\Event\FindActionEvent;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Component\Router\RouterInterface;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
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

    public function __construct(
        private SharedConfig $config,
    ) {
        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\Router');
        $this->class = $namespace->addClass('FindActionListener');

        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

        return new Twig\Environment($loader);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError|RouterGeneratorException
     */
    public function write(): void
    {
        $code = $this->twig->render('find_action.php.twig', [
            'service' => $this->config->newInstanceGenerator->generate(RouterInterface::class, RouterInterface::class),
        ]);

        $method = $this->class->addMethod('invoke');
        $method->setVisibility(ClassLike::VisibilityPublic);
        $method->setReturnType('void');
        $method->setBody($code);

        $parameter = $method->addParameter('event');
        $parameter->setType(FindActionEvent::class);

        $this->writeFile();
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
            $directory . '/FindActionListener.php',
            (new PsrPrinter())->printFile($this->file),
        );
    }
}
