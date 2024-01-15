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

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    private function addGetColumnNamesMethod(): void
    {
        $columnNames = [];
        foreach ($this->entityMetadata->fields as $fieldMetadata) {
            $columnNames[] = $fieldMetadata->columnName;
        }

        $code = 'return ' . var_export($columnNames, true) . ';';

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'getColumnNames',
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
            name: 'hydrate',
            returnType: 'void',
            code: implode("\n\n", $code),
            parameters: [new Parameter(type: 'mixed', name: 'values')],
        );
    }
}
