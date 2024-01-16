<?php

namespace Kaa\Component\Database\Writer\EntityManagerWriter;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\EntityInterface;
use Kaa\Component\Database\EntityManager\AbstractPdoMysqlEntityManager;
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
readonly class PdoMysqlEntityManagerWriter implements EntityManagerWriterInterface
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    public function __construct(
        private string $connectionName,
        /** @var EntityMetadata[] */
        private array $entityMetadata,
        private SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'Database',
            className: 'EntityManager' . $connectionName,
            extends: AbstractPdoMysqlEntityManager::class,
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../../templates');
    }

    /**
     * @throws LoaderError|RuntimeError|SyntaxError|WriterException
     */
    public function write(): void
    {
        $this->addFindMethod();
        $this->addFindOneByMethod();
        $this->addFindByMethod();
        $this->addRefreshMethod();
        $this->addNewMethod();

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addFindMethod(): void
    {
        $code = $this->twig->render('pdo_mysql/find.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'find',
            returnType: EntityInterface::class . '|null',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
                new Parameter(type: 'int', name: 'id'),
            ],
            comment: '
                @template T of \Kaa\Component\Database\EntityInterface
                @kphp-generic T
            
                @param class-string<T> $entityClass
                @return T|null
            ',
        );
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addFindOneByMethod(): void
    {
        $code = $this->twig->render('pdo_mysql/find_one_by.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'findOneBy',
            returnType: EntityInterface::class . '|null',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
                new Parameter(type: 'array', name: 'criteria'),
            ],
            comment: '
                @template T of \Kaa\Component\Database\EntityInterface
                @kphp-generic T
                
                @param array<string, string|int> $criteria
                @param class-string<T> $entityClass
                @return T|null
            ',
        );
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addFindByMethod(): void
    {
        $code = $this->twig->render('pdo_mysql/find_by.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'findBy',
            returnType: 'array',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
                new Parameter(type: 'array', name: 'criteria'),
                new Parameter(type: 'array', name: 'order', defaultValue: []),
                new Parameter(type: 'int', name: 'limit', nullable: true, defaultValue: null),
                new Parameter(type: 'int', name: 'offset', nullable: true, defaultValue: null),
            ],
            comment: '
                @template T of \Kaa\Component\Database\EntityInterface
                @kphp-generic T
                
                @param array<string, string|int> $criteria
                @param array<string, string> $order
                @param class-string<T> $entityClass
                @return T[]
            ',
        );
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addRefreshMethod(): void
    {
        $code = $this->twig->render('pdo_mysql/refresh.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'refresh',
            returnType: 'void',
            code: $code,
            parameters: [
                new Parameter(type: EntityInterface::class, name: 'entity'),
            ],
        );
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    private function addNewMethod(): void
    {
        $code = $this->twig->render('new.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'new',
            returnType: 'object',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
            ],
            comment: '
                @template T
                @kphp-generic T
            
                @param class-string<T> $entityClass
                @return T
            ',
        );
    }
}
