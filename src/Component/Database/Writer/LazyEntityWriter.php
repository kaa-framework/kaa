<?php

namespace Kaa\Component\Database\Writer;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Util\Reflection;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\None;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class LazyEntityWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    public function __construct(
        string $connectionName,
        private EntityMetadata $entityMetadata,
        private SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: "Database\\LazyEntity\\{$connectionName}",
            className: $this->entityMetadata->className,
            extends: "\\Kaa\\Generated\\Database\\Entity\\{$connectionName}\\{$this->entityMetadata->className}",
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws BadTypeException|ReflectionException|LoaderError|RuntimeError|SyntaxError|WriterException
     */
    public function write(): void
    {
        $this->addConstructor();
        $this->overrideMethods();
        $this->addIsInitializedMethod();
        $this->addSetInitializedMethod();
        $this->addVariables();

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws SyntaxError|ReflectionException|BadTypeException|RuntimeError|LoaderError
     */
    private function overrideMethods(): void
    {
        $class = new ReflectionClass($this->entityMetadata->entityClass);

        foreach ($class->getMethods() as $method) {
            if ($method->isAbstract() || str_starts_with($method->getName(), '_')) {
                continue;
            }

            $parameters = [];
            $forwardParameters = [];
            foreach ($method->getParameters() as $parameter) {
                $parameters[] = new Parameter(
                    type: Reflection::namedType($parameter->getType())->getName(),
                    name: $parameter->getName(),
                    defaultValue: $parameter->isOptional() ? $parameter->getDefaultValue() : None::None,
                );

                $forwardParameters[] = $parameter->getName();
            }

            $code = $this->twig->render('lazy_method.php.twig', [
                'parameters' => $forwardParameters,
                'isVoid' => Reflection::namedType($method->getReturnType())->getName() === 'void',
                'name' => $method->getName(),
            ]);

            $this->classWriter->addMethod(
                visibility: $this->getVisibility($method),
                name: $method->getName(),
                returnType: Reflection::namedType($method->getReturnType())->getName(),
                code: $code,
                parameters: $parameters,
                comment: $method->getDocComment() !== false ? $method->getDocComment() : null,
            );
        }
    }

    private function getVisibility(ReflectionMethod $method): Visibility
    {
        return match (true) {
            $method->isPublic() => Visibility::Public,
            $method->isProtected() => Visibility::Protected,
            default => Visibility::Private,
        };
    }

    private function addConstructor(): void
    {
        $code = '$this->' . $this->entityMetadata->idFieldName . ' = $id;';
        $code .= "\n";
        $code .= '$this->entityManager = $entityManager;';

        $this->classWriter->addConstructor(
            visibility: Visibility::Public,
            code: $code,
            parameters: [
                new Parameter(type: 'int', name: 'id'),
                new Parameter(type: EntityManagerInterface::class, name: 'entityManager'),
            ],
        );
    }

    private function addVariables(): void
    {
        $this->classWriter->addVariable(
            visibility: Visibility::Private,
            type: 'bool',
            name: 'initialized',
            value: false,
        );

        $this->classWriter->addVariable(
            visibility: Visibility::Private,
            type: EntityManagerInterface::class,
            name: 'entityManager',
        );
    }

    private function addIsInitializedMethod(): void
    {
        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_isInitialized',
            returnType: 'bool',
            code: 'return $this->initialized;',
        );
    }

    private function addSetInitializedMethod(): void
    {
        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_setInitialized',
            returnType: 'void',
            code: '$this->initialized = true;',
        );
    }
}
