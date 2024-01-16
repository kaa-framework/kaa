<?php

namespace Kaa\Component\Database\Writer;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;
use Kaa\Component\Database\Exception\DatabaseGeneratorException;
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
     * @throws BadTypeException|ReflectionException|LoaderError|RuntimeError|SyntaxError|WriterException|DatabaseGeneratorException
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

    private function addConstructor(): void
    {
        $code = "parent::__construct();\n";
        $code .= '$this->' . $this->entityMetadata->idFieldName . ' = $id;' . "\n";
        $code .= '$this->_entityManager = $entityManager;';

        $this->classWriter->addConstructor(
            visibility: Visibility::Public,
            code: $code,
            parameters: [
                new Parameter(type: 'int', name: 'id'),
                new Parameter(type: EntityManagerInterface::class, name: 'entityManager'),
            ],
        );
    }

    /**
     * @throws SyntaxError|ReflectionException|BadTypeException|RuntimeError|LoaderError|DatabaseGeneratorException
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
                    nullable: $parameter->allowsNull(),
                    defaultValue: $parameter->isOptional() ? $parameter->getDefaultValue() : None::None,
                );

                $forwardParameters[] = $parameter->getName();
            }

            $code = $this->twig->render('lazy_method.php.twig', [
                'parameters' => $forwardParameters,
                'isVoid' => Reflection::namedType($method->getReturnType())->getName() === 'void',
                'name' => $method->getName(),
            ]);

            $returnType = Reflection::namedType($method->getReturnType())->getName();
            if ($returnType === 'self' || $returnType === 'static') {
                throw new DatabaseGeneratorException("You must not use self or static as a return type in {$method->getDeclaringClass()->getName()}::{$method->getName()}");
            }

            if (Reflection::namedType($method->getReturnType())->allowsNull()) {
                $returnType .= '|null';
            }

            $this->classWriter->addMethod(
                visibility: $this->getVisibility($method),
                name: $method->getName(),
                returnType: $returnType,
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

    private function addVariables(): void
    {
        $this->classWriter->addVariable(
            visibility: Visibility::Private,
            type: 'bool',
            name: 'initialized',
            value: false,
            comment: '@kphp-json skip'
        );

        $this->classWriter->addVariable(
            visibility: Visibility::Private,
            type: EntityManagerInterface::class,
            name: '_entityManager',
            comment: '@kphp-json skip'
        );
    }
}
