<?php

namespace Kaa\Component\Database\Writer;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;
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
readonly class EntityWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    public function __construct(
        private string $connectionName,
        private EntityMetadata $entityMetadata,
        private SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: "Database\\Entity\\{$connectionName}",
            className: $this->entityMetadata->className,
            extends: $this->entityMetadata->entityClass,
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws LoaderError|RuntimeError|SyntaxError|WriterException
     */
    public function write(): void
    {
        $this->addConstructor();
        $this->addGetColumnNamesMethod();
        $this->addHydrateMethod();
        $this->addHydrateOneToManyMethod();
        $this->addGetValuesMethod();
        $this->addGetId();
        $this->addSetId();
        $this->addGetIdColumnName();
        $this->addGetTableName();
        $this->addIsInitializedMethod();
        $this->addSetInitializedMethod();
        $this->addGetOidMethod();
        $this->addOidVariable();
        $this->addGetNotInsertedOidsMethod();

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    private function addConstructor(): void
    {
        $this->classWriter->addConstructor(
            visibility: Visibility::Public,
            code: '$this->_oid = microtime() . "#" . mt_rand();',
        );
    }

    private function addGetColumnNamesMethod(): void
    {
        $columnNames = [];
        foreach ($this->entityMetadata->fields as $fieldMetadata) {
            if ($fieldMetadata->isId) {
                continue;
            }

            $columnNames[] = $fieldMetadata->columnName;
        }

        foreach ($this->entityMetadata->manyToOne as $manyToOneMetadata) {
            $columnNames[] = $manyToOneMetadata->columnName;
        }

        $code = 'return ' . var_export($columnNames, true) . ';';

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getColumnNames',
            returnType: 'array',
            code: $code,
            comment: '@return string[]',
        );
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    private function addHydrateMethod(): void
    {
        $code = $this->twig->render('hydrate.php.twig', [
            'fields' => $this->entityMetadata->fields,
            'manyToOne' => $this->entityMetadata->manyToOne,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_hydrate',
            returnType: 'array',
            code: $code,
            parameters: [
                new Parameter(type: 'mixed', name: 'values'),
                new Parameter(type: EntityManagerInterface::class, name: 'entityManager'),
                new Parameter(type: 'array', name: 'managedEntities'),
            ],
            comment: '@param array<string, \Kaa\Component\Database\Dto\EntityWithValueSet> $managedEntities' . "\n" . '@return \Kaa\Component\Database\EntityInterface[]',
        );
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    private function addHydrateOneToManyMethod(): void
    {
        $code = $this->twig->render('hydrate_one_to_many.php.twig', [
            'fields' => $this->entityMetadata->fields,
            'oneToMany' => $this->entityMetadata->oneToMany,
            'connection' => $this->connectionName,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_hydrateOneToMany',
            returnType: 'array',
            code: $code,
            parameters: [
                new Parameter(type: 'array', name: 'ids'),
                new Parameter(type: EntityManagerInterface::class, name: 'entityManager'),
                new Parameter(type: 'array', name: 'managedEntities'),
            ],
            comment: '
                @internal
                @param array<string, int[]> $ids
                @param array<string, \Kaa\Component\Database\Dto\EntityWithValueSet> $managedEntities
                @return \Kaa\Component\Database\EntityInterface[] Сущности, которые должны стать managed
            ',
        );
    }

    private function addGetValuesMethod(): void
    {
        $code = ['$values = [];'];

        foreach ($this->entityMetadata->fields as $fieldMetadata) {
            if ($fieldMetadata->isId) {
                continue;
            }

            $valueCode = $fieldMetadata->type->getSerializer()->getSerializationCode(
                fieldCode: '$this->' . $fieldMetadata->name,
                phpType: $fieldMetadata->phpType,
                isNullable: $fieldMetadata->isNullable,
            );

            $code[] = sprintf(
                '$values["%s"] = %s;',
                $fieldMetadata->columnName,
                $valueCode,
            );
        }

        foreach ($this->entityMetadata->manyToOne as $manyToOneMetadata) {
            $valueCode = '$this->' . $manyToOneMetadata->fieldName . '->_getId()';

            $code[] = sprintf(
                '$values["%s"] = %s;',
                $manyToOneMetadata->columnName,
                $valueCode,
            );
        }

        $code[] = 'return $values;';

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getValues',
            returnType: 'mixed',
            code: implode("\n\n", $code),
        );
    }

    private function addGetId(): void
    {
        $code = 'return $this->' . $this->entityMetadata->idFieldName . ';';

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getId',
            returnType: 'int|null',
            code: $code,
        );
    }

    private function addSetId(): void
    {
        $code = '$this->' . $this->entityMetadata->idFieldName . ' = $id;';

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_setId',
            returnType: 'void',
            code: $code,
            parameters: [new Parameter(type: 'int', name: 'id')],
        );
    }

    private function addGetIdColumnName(): void
    {
        $code = sprintf('return "%s";', $this->entityMetadata->idColumnName);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getIdColumnName',
            returnType: 'string',
            code: $code,
        );
    }

    private function addGetTableName(): void
    {
        $code = sprintf('return "%s";', $this->entityMetadata->tableName);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getTableName',
            returnType: 'string',
            code: $code,
        );
    }

    private function addIsInitializedMethod(): void
    {
        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_isInitialized',
            returnType: 'bool',
            code: 'return true;',
        );
    }

    private function addSetInitializedMethod(): void
    {
        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_setInitialized',
            returnType: 'void',
            code: '',
        );
    }

    private function addGetOidMethod(): void
    {
        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getOid',
            returnType: 'string',
            code: 'return $this->_oid;',
        );
    }

    private function addOidVariable(): void
    {
        $this->classWriter->addVariable(
            visibility: Visibility::Private,
            type: 'string',
            name: '_oid',
        );
    }

    private function addGetNotInsertedOidsMethod(): void
    {
        $code = $this->twig->render('get_not_inserted_oids.php.twig', [
            'manyToOne' => $this->entityMetadata->manyToOne,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getNotInsertedOids',
            returnType: 'array',
            code: $code,
            comment: '@return string[]',
        );
    }
}
