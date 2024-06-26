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

    public function remove(EntityInterface $entity): void
    {
        $this->managedEntities[$entity->_getEntityClassName() . '#' . $entity->_getId()]->getEntity()->_setIsQueuedToRemove();
    }

    /**
     * @throws Throwable
     */
    public function flush(): void
    {
        $this->delete();

        $sort = new DnfSort();
        foreach ($this->newEntities as $entity) {
            $sort->addNode($entity);
        }

        foreach ($this->newEntities as $entity) {
            foreach ($entity->_getNotInsertedOids() as $oid) {
                $sort->addEdge($entity->_getOid(), $oid);
            }
        }

        $this->insert($sort->sort());
        $this->update();

        foreach ($this->managedEntities as $managedEntity) {
            if (!$managedEntity->getEntity()->_isInitialized()) {
                continue;
            }

            $managedEntity->setValues($managedEntity->getEntity()->_getValues());
        }

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

    /**
     * @return array<string, \Kaa\Component\Database\Dto\EntityWithValueSet>
     */
    public function _getManagedEntities(): array
    {
        return $this->managedEntities;
    }

    /**
     * @internal
     */
    public function _addManagedEntity(string $key, EntityWithValueSet $object): self
    {
        $this->managedEntities[$key] = $object;

        return $this;
    }

    /**
     * @param EntityInterface[] $newEntities
     */
    abstract protected function insert(array $newEntities): void;

    abstract protected function update(): void;

    abstract protected function delete(): void;
}
