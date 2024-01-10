<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Writer;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\Service;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Dto\Services;
use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ContainerWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;
    private Services $services;

    public function __construct(
        private SharedConfig $config,
        private ServiceCollection $serviceCollection,
        private ParameterCollection $parameterCollection,
        private AliasCollection $aliasCollection,
    ) {
        $this->services = new Services($this->serviceCollection, $this->aliasCollection);

        $this->classWriter = new ClassWriter(
            namespaceName: 'DependencyInjection',
            className: 'Container',
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates', $this->getTwigFilters());
    }

    /**
     * @return Twig\TwigFilter[]
     */
    private function getTwigFilters(): array
    {
        return [
            new Twig\TwigFilter(
                'literal',
                static fn (mixed $value) => is_string($value) ? "'" . $value . "'" : $value,
            ),

            new Twig\TwigFilter(
                'methodName',
                $this->serviceNameToMethodName(...),
            ),

            new Twig\TwigFilter(
                'varName',
                $this->serviceNameToVariableName(...),
            ),
        ];
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError|WriterException
     */
    public function write(): void
    {
        foreach ($this->serviceCollection as $service) {
            $this->addServiceMethod($service);
        }

        $this->addGetMethod();
        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addServiceMethod(Service $service): void
    {
        if ($service->isSingleton) {
            $this->classWriter->addVariable(
                visibility: Visibility::Private,
                type: '\\' . $service->class->getName(),
                name: $this->serviceNameToVariableName($service->name),
                nullable: true,
                value: null,
                isStatic: true,
            );
        }

        $code = $this->twig->render('body.php.twig', [
            'service' => $service,
            'services' => $this->services,
            'parameters' => $this->parameterCollection,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Private,
            name: $this->serviceNameToMethodName($service->name),
            returnType: '\\' . $service->class->getName(),
            code: $code,
            isStatic: true,
        );
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addGetMethod(): void
    {
        $code = $this->twig->render('switch.php.twig', [
            'classesToServices' => $this->serviceCollection->getClassesToServices(),
            'aliases' => $this->aliasCollection,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'get',
            returnType: 'object',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'nameOrAlias'),
                new Parameter(type: 'string', name: 'class'),
            ],
            isStatic: true,
            comment: $this->twig->render('comment.php.twig'),
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
}
