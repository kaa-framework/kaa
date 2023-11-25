<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\Service;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Dto\Services;
use Kaa\Component\DependencyInjection\Exception\DependencyInjectionGeneratorException;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ContainerWriter
{
    private PhpFile $file;
    private ClassType $class;

    private Twig\Environment $twig;

    private Services $services;

    public function __construct(
        private SharedConfig $config,
        private ServiceCollection $serviceCollection,
        private ParameterCollection $parameterCollection,
        private AliasCollection $aliasCollection,
    ) {
        $this->services = new Services($this->serviceCollection, $this->aliasCollection);

        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\DependencyInjection');
        $this->class = $namespace->addClass('Container');

        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new Twig\Environment($loader);

        $twig->addFilter(
            new Twig\TwigFilter(
                'literal',
                static fn (mixed $value) => is_string($value) ? "'" . $value . "'" : $value,
            ),
        );

        $twig->addFilter(
            new Twig\TwigFilter(
                'methodName',
                $this->serviceNameToMethodName(...),
            ),
        );

        $twig->addFilter(
            new Twig\TwigFilter(
                'varName',
                $this->serviceNameToVariableName(...),
            ),
        );

        return $twig;
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError|DependencyInjectionGeneratorException
     */
    public function write(): void
    {
        foreach ($this->serviceCollection as $service) {
            $this->addServiceMethod($service);
        }

        $this->addGetMethod();

        $this->writeFile();
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addServiceMethod(Service $service): void
    {
        if ($service->isSingleton) {
            $this->addVar(
                '\\' . $service->class->getName(),
                $this->serviceNameToVariableName($service->name),
            );
        }

        $this->addMethod(
            $this->serviceNameToMethodName($service->name),
            '\\' . $service->class->getName(),
            $this->twig->render('body.php.twig', [
                'service' => $service,
                'services' => $this->services,
                'parameters' => $this->parameterCollection,
            ]),
        );
    }

    private function serviceNameToMethodName(string $serviceName): string
    {
        return 'get' . $this->serviceNameToVariableName($serviceName);
    }

    private function serviceNameToVariableName(string $serviceName): string
    {
        return str_replace(['\\', '.'], '_', $serviceName);
    }

    private function addVar(string $type, string $name): void
    {
        $var = $this->class->addProperty($name, null);
        $var->setNullable();

        $var->setType($type);
        $var->setStatic();
        $var->setValue(null);
        $var->setVisibility(ClassLike::VisibilityPrivate);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addGetMethod(): void
    {
        $method = $this->addMethod(
            'get',
            'object',
            $this->twig->render('switch.php.twig', [
                'classesToServices' => $this->serviceCollection->getClassesToServices(),
                'aliases' => $this->aliasCollection,
            ]),
            ClassLike::VisibilityPublic,
        );

        $method->addParameter('nameOrAlias')->setType('string');
        $method->addParameter('class')->setType('string');
        $method->addComment($this->twig->render('comment.php.twig'));
    }

    private function addMethod(
        string $name,
        string $type,
        string $code,
        string $visibility = ClassLike::VisibilityPrivate,
    ): Method {
        $method = $this->class->addMethod($name);
        $method->setReturnType($type);
        $method->setStatic();
        $method->setVisibility($visibility);
        $method->setBody($code);

        return $method;
    }

    /**
     * @throws DependencyInjectionGeneratorException
     */
    private function writeFile(): void
    {
        $directory = $this->config->exportDirectory . '/DependencyInjection';

        if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
            throw new DependencyInjectionGeneratorException("Directory {$directory} was not created");
        }

        file_put_contents(
            $directory . '/Container.php',
            (new PsrPrinter())->printFile($this->file),
        );
    }
}
