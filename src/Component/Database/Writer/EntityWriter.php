<?php

namespace Kaa\Component\Database\Writer;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\Visibility;

#[PhpOnly]
readonly class EntityWriter
{
    private ClassWriter $classWriter;

    public function __construct(
        string $connectionName,
        private EntityMetadata $entityMetadata,
        private SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: "Database\\Entity\\{$connectionName}",
            className: $this->entityMetadata->className,
            extends: $this->entityMetadata->entityClass,
        );
    }

    /**
     * @throws WriterException
     */
    public function write(): void
    {
        $this->addGetColumnNamesMethod();
        $this->addHydrateMethod();
        $this->addGetValuesMethod();
        $this->addGetId();
        $this->addSetId();
        $this->addGetIdColumnName();
        $this->addGetTableName();

        $this->classWriter->writeFile($this->config->exportDirectory);
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

        $code = 'return ' . var_export($columnNames, true) . ';';

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_getColumnNames',
            returnType: 'array',
            code: $code,
            comment: '@return string[]',
        );
    }

    private function addHydrateMethod(): void
    {
        $code = [];

        foreach ($this->entityMetadata->fields as $field) {
            $code[] = $field->type->getHydrator()->getHydrationCode(
                fieldCode: '$this->' . $field->name,
                phpType: $field->phpType,
                isNullable: $field->isNullable,
                valueCode: '$values["' . $field->columnName . '"]',
            );
        }

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: '_hydrate',
            returnType: 'void',
            code: implode("\n\n", $code),
            parameters: [new Parameter(type: 'mixed', name: 'values')],
        );
    }

    private function addGetValuesMethod(): void
    {
        $code = ['$values = [];'];

        foreach ($this->entityMetadata->fields as $field) {
            if ($field->isId) {
                continue;
            }

            $valueCode = $field->type->getSerializer()->getSerializationCode(
                fieldCode: '$this->' . $field->name,
                phpType: $field->phpType,
                isNullable: $field->isNullable,
            );

            $code[] = sprintf(
                '$values["%s"] = %s;',
                $field->columnName,
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
}
