<?php

namespace Kaa\Component\Database\EntityManager;

use Kaa\Component\Database\Dto\EntityWithValueSet;
use Kaa\Component\Database\EntityInterface;
use Throwable;

abstract class AbstractEntityManager implements EntityManagerInterface
{
    /** @var EntityInterface[] */
    protected array $newEntities = [];

    /** @var array<string, EntityWithValueSet> */
    protected array $managedEntities = [];

    /**
     * @throws Throwable
     */
    public function flush(): void
    {
        $this->insert();
        $this->update();

        foreach ($this->newEntities as $entity) {
            $this->managedEntities[get_class($entity) . '#' . $entity->_getId()] = new EntityWithValueSet(
                $entity,
                $entity->_getValues()
            );
        }

        $this->newEntities = [];
    }

    protected function getChangeset(mixed $new, mixed $old): mixed
    {
        $changeset = [];

        foreach ($new as $column => $value) {
            if ($old[$column] !== $value) {
                $changeset[$column] = $value;
            }
        }

        return $changeset;
    }

    abstract protected function insert(): void;

    abstract protected function update(): void;
}
