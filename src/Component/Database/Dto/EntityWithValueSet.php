<?php

namespace Kaa\Component\Database\Dto;

use Kaa\Component\Database\EntityInterface;

class EntityWithValueSet
{
    private EntityInterface $entity;

    private mixed $values;

    public function __construct(
        EntityInterface $entity,
        mixed $values
    ) {
        $this->entity = $entity;
        $this->values = $values;
    }

    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    public function getValues(): mixed
    {
        return $this->values;
    }

    public function setValues(mixed $values): self
    {
        $this->values = $values;

        return $this;
    }
}
