<?php

namespace Kaa\Component\Database\Finder;

use Kaa\Component\Database\Attribute\Column;
use Kaa\Component\Database\Attribute\Entity;
use Kaa\Component\Database\Attribute\Id;
use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\Dto\FieldMetadata;
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
     * @throws ReflectionException|FinderException
     */
    public function getMetadata(): array
    {
        $entityClasses = ClassFinder::find(
            scan: $this->scan,
            predicate: static fn (ReflectionClass $c) => $c->getAttributes(Entity::class) !== [],
        );

        return array_map($this->getEntityMetadata(...), $entityClasses);
    }

    /**
     * @throws DatabaseGeneratorException
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

        $idColumnName = $idField->getAttributes(Column::class)[0]->newInstance()->name
            ?? $this->namingStrategy->getColumnName($idField->getName());

        return new EntityMetadata(
            entityClass: $class->getName(),
            className: $class->getShortName(),
            tableName: $entityAttribute->table ?? $this->namingStrategy->getTableName($class->getName()),
            fields: array_map($this->getFieldMetadata(...), $mappedFields),
            idColumnName: $idColumnName,
        );
    }

    /**
     * @throws BadTypeException
     */
    private function getFieldMetadata(ReflectionProperty $property): FieldMetadata
    {
        /** @var Column $columnAttribute */
        $columnAttribute = $property->getAttributes(Column::class)[0]->newInstance();

        return new FieldMetadata(
            name: $property->getName(),
            columnName: $columnAttribute->name ?? $this->namingStrategy->getColumnName($property->getName()),
            type: $columnAttribute->type,
            phpType: Reflection::namedType($property->getType())->getName(),
            isNullable: $columnAttribute->nullable
        );
    }
}
