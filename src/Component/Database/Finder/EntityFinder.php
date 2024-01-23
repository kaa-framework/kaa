<?php

namespace Kaa\Component\Database\Finder;

use Kaa\Component\Database\Attribute\Column;
use Kaa\Component\Database\Attribute\Entity;
use Kaa\Component\Database\Attribute\Id;
use Kaa\Component\Database\Attribute\ManyToOne;
use Kaa\Component\Database\Attribute\OneToMany;
use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\Dto\FieldMetadata;
use Kaa\Component\Database\Dto\ManyToOneMetadata;
use Kaa\Component\Database\Dto\OneToManyMetadata;
use Kaa\Component\Database\EntityInterface;
use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Database\NamingStrategy\NamingStrategyInterface;
use Kaa\Component\Database\NamingStrategy\UnderscoreNamingStrategy;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\Exception\FinderException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\ClassFinder;
use Kaa\Component\Generator\Util\Reflection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[PhpOnly]
readonly class EntityFinder
{
    private NamingStrategyInterface $namingStrategy;

    /**
     * @param class-string<NamingStrategyInterface>|null $namingStrategyClass
     */
    public function __construct(
        /** @var string[] */
        private array $scan,
        ?string $namingStrategyClass,
    ) {
        $this->namingStrategy = $namingStrategyClass !== null
            ? new $namingStrategyClass()
            : new UnderscoreNamingStrategy();
    }

    /**
     * @return EntityMetadata[]
     *
     * @throws BadTypeException|DatabaseGeneratorException|FinderException|ReflectionException
     */
    public function getMetadata(): array
    {
        $entityClasses = ClassFinder::find(
            scan: $this->scan,
            predicate: static fn (ReflectionClass $c) => $c->getAttributes(Entity::class) !== [],
        );

        $entityMetadata = [];
        foreach ($entityClasses as $entityClass) {
            $entityMetadata[$entityClass->getName()] = $this->getEntityMetadata($entityClass);
        }

        return $this->finishOneToManyMappings($entityMetadata);
    }

    /**
     * @throws DatabaseGeneratorException|BadTypeException
     */
    private function getEntityMetadata(ReflectionClass $class): EntityMetadata
    {
        if ($class->getConstructor() !== null) {
            throw new DatabaseGeneratorException("Entity {$class->getName()} must not have a constructor");
        }

        $implementsEntityInterface = false;

        $interfaces = $class->getInterfaces();
        foreach ($interfaces as $interface) {
            if ($interface->getName() === EntityInterface::class) {
                $implementsEntityInterface = true;
                break;
            }
        }

        if (!$implementsEntityInterface) {
            throw new DatabaseGeneratorException("Entity {$class->getName()} must implement " . EntityInterface::class);
        }

        /** @var Entity $entityAttribute */
        $entityAttribute = $class->getAttributes(Entity::class)[0]->newInstance();

        $mappedFields = array_filter(
            $class->getProperties(),
            static fn (ReflectionProperty $p) => $p->getAttributes(Column::class) !== [],
        );

        $idField = array_filter(
            $mappedFields,
            static fn (ReflectionProperty $p) => $p->getAttributes(Id::class) !== [],
        )[0] ?? null;

        if ($idField === null) {
            throw new DatabaseGeneratorException("Entity {$class->getName()} does not have an Id field");
        }

        if (Reflection::namedType($idField->getType())->getName() !== 'int') {
            throw new DatabaseGeneratorException("Id field must have type int in {$class->getName()}::{$idField->getName()}");
        }

        $idColumnName = $idField->getAttributes(Column::class)[0]->newInstance()->name
            ?? $this->namingStrategy->getColumnName($idField->getName());

        $manyToOneFields = array_filter(
            $class->getProperties(),
            static fn (ReflectionProperty $p) => $p->getAttributes(ManyToOne::class) !== [],
        );

        $oneToManyFields = array_filter(
            $class->getProperties(),
            static fn (ReflectionProperty $p) => $p->getAttributes(OneToMany::class) !== [],
        );

        return new EntityMetadata(
            entityClass: $class->getName(),
            className: $class->getShortName(),
            tableName: $entityAttribute->table ?? $this->namingStrategy->getTableName($class->getName()),
            idColumnName: $idColumnName,
            idFieldName: $idField->getName(),
            fields: array_map($this->getFieldMetadata(...), $mappedFields),
            manyToOne: array_map($this->getManyToOneMetadata(...), $manyToOneFields),
            oneToMany: array_map($this->getOneToManyMetadata(...), $oneToManyFields),
        );
    }

    /**
     * @throws BadTypeException|DatabaseGeneratorException
     */
    private function getFieldMetadata(ReflectionProperty $property): FieldMetadata
    {
        if ($property->isPrivate()) {
            throw new DatabaseGeneratorException("Annotated property {$property->getDeclaringClass()->getName()}::{$property->getName()} must not be private");
        }

        /** @var Column $columnAttribute */
        $columnAttribute = $property->getAttributes(Column::class)[0]->newInstance();

        return new FieldMetadata(
            name: $property->getName(),
            columnName: $columnAttribute->name ?? $this->namingStrategy->getColumnName($property->getName()),
            type: $columnAttribute->type,
            phpType: Reflection::namedType($property->getType())->getName(),
            isNullable: $columnAttribute->nullable,
            isId: $property->getAttributes(Id::class) !== [],
        );
    }

    /**
     * @throws DatabaseGeneratorException|ReflectionException
     */
    private function getManyToOneMetadata(ReflectionProperty $property): ManyToOneMetadata
    {
        if ($property->isPrivate()) {
            throw new DatabaseGeneratorException("Annotated property {$property->getDeclaringClass()->getName()}::{$property->getName()} must not be private");
        }

        /** @var ManyToOne $attribute */
        $attribute = $property->getAttributes(ManyToOne::class)[0]->newInstance();

        return new ManyToOneMetadata(
            fieldName: $property->getName(),
            targetEntity: $attribute->targetEntity,
            targetEntityClassName: (new ReflectionClass($attribute->targetEntity))->getShortName(),
            columnName: $attribute->columnName ?? $this->namingStrategy->getColumnName($property->getName()) . '_id',
            isNullable: $attribute->nullable,
        );
    }

    /**
     * @throws DatabaseGeneratorException|ReflectionException
     */
    private function getOneToManyMetadata(ReflectionProperty $property): OneToManyMetadata
    {
        if ($property->isPrivate()) {
            throw new DatabaseGeneratorException("Annotated property {$property->getDeclaringClass()->getName()}::{$property->getName()} must not be private");
        }

        if (!$property->hasDefaultValue() && $property->getDefaultValue() !== []) {
            throw new DatabaseGeneratorException("OneToMany property must have default value '[]' in {$property->getDeclaringClass()->getName()}::{$property->getName()}");
        }

        /** @var OneToMany $attribute */
        $attribute = $property->getAttributes(OneToMany::class)[0]->newInstance();

        return new OneToManyMetadata(
            fieldName: $property->getName(),
            targetEntity: $attribute->targetEntity,
            targetEntityClassName: (new ReflectionClass($attribute->targetEntity))->getShortName(),
            referenceFieldName: $attribute->mappedBy,
        );
    }

    /**
     * @param array<string, EntityMetadata> $entityMetadata
     * @return array<string, EntityMetadata>
     * @throws DatabaseGeneratorException
     */
    private function finishOneToManyMappings(array $entityMetadata): array
    {
        foreach ($entityMetadata as $entity) {
            foreach ($entity->oneToMany as $oneToMany) {
                if (!array_key_exists($oneToMany->targetEntity, $entityMetadata)) {
                    throw new DatabaseGeneratorException(
                        sprintf(
                            'Referenced OneToMany entity %s does not exist in %s::%s',
                            $oneToMany->targetEntity,
                            $entity->className,
                            $oneToMany->fieldName,
                        )
                    );
                }

                $oneToMany->targetEntityTable = $entityMetadata[$oneToMany->targetEntity]->tableName;
                $oneToMany->targetEntityIdColumnName = $entityMetadata[$oneToMany->targetEntity]->idColumnName;

                foreach ($entityMetadata[$oneToMany->targetEntity]->manyToOne as $manyToOne) {
                    if (
                        $manyToOne->targetEntity !== $entity->entityClass
                        || $manyToOne->fieldName !== $oneToMany->referenceFieldName
                    ) {
                        continue;
                    }

                    $oneToMany->referenceColumnName = $manyToOne->columnName;
                    break;
                }

                if ($oneToMany->referenceColumnName === null) {
                    throw new DatabaseGeneratorException("Referenced OneToMany field {$oneToMany->targetEntity}::{$oneToMany->referenceFieldName} does not exist in {$entity->className}::{$oneToMany->fieldName}");
                }
            }
        }

        return $entityMetadata;
    }
}
