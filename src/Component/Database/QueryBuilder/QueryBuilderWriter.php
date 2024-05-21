<?php

declare(strict_types=1);

namespace Kaa\Component\Database\QueryBuilder;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\EntityInterface;
use Kaa\Component\Database\QueryBuilder\Query\Dto\EntityInfo;
use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Twig;

#[PhpOnly]
readonly class QueryBuilderWriter
{
    private ClassWriter $classWriter;

    private Twig\Environment $twig;

    /**
     * @param EntityMetadata[] $entityMetadata
     */
    public function __construct(
        private string $connectionName,
        private array $entityMetadata,
        private SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: "Database\\QueryBuilder\\{$connectionName}",
            className: 'QueryBuilder' . $connectionName,
            extends: AbstractQueryBuilder::class,
        );

        $this->twig = TwigFactory::create(dirname(__DIR__, 1) . '/templates/query_builder');
    }

    /**
     * @throws WriterException
     */
    public function write(): void
    {
        $this->addGetEntityInfoMethod();
        $this->addGetEntityColumns();
        $this->addGetResultMethod();
        $this->addGetHydrateEntity();

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    public function addGetEntityInfoMethod(): void
    {
        $code = $this->twig->render('get_entity_info.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
            'infoClassName' => EntityInfo::class
        ]);
        $this->classWriter->addMethod(
            visibility: Visibility::Protected,
            name: 'getEntityInfo',
            returnType: EntityInfo::class,
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
            ],
        );
    }

    public function addGetEntityColumns(): void
    {
        $code = $this->twig->render('get_entity_columns.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
            'namespace' => '\\Kaa\\Generated\\Database\\Entity'
        ]);
        $this->classWriter->addMethod(
            visibility: Visibility::Protected,
            name: 'getEntityColumns',
            returnType: 'array',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
            ],
            comment: '
                @return string[]
            ',
        );
    }

    public function addGetResultMethod(): void
    {
        $code = $this->twig->render('get_result.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'getResult',
            returnType: 'array',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass')
            ],
            comment: '
                @template T of \Kaa\Component\Database\EntityInterface
                @kphp-generic T
                @param class-string<T> $entityClass
                @return T[]
            ',
        );
    }

    public function addGetHydrateEntity(): void
    {
        $code = $this->twig->render('get_hydrate_entity.php.twig', [
            'entities' => $this->entityMetadata,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'getHydrateEntity',
            returnType: EntityInterface::class,
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'entityClass'),
                new Parameter('array', 'result')
            ],
            comment: '
                @template T of \Kaa\Component\Database\EntityInterface
                @kphp-generic T
                @param class-string<T> $entityClass
                @param mixed[] $result
                @return T
            ',
        );
    }
}
