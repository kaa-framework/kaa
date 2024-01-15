<?php

namespace Kaa\Component\Database\Writer\EntityManagerWriter;

use Kaa\Component\Database\Dto\EntityMetadata;
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
        $this->addFind();

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addFind(): void
    {
        $code = $this->twig->render('pdo_mysql/find.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'find',
            returnType: 'object|null',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
                new Parameter(type: 'int', name: 'id'),
            ],
            comment: '
                @template T
                @kphp-generic T
            
                @param class-string<T> $entityClass
                @return T|null
            ',
        );
    }
}
