<?php

namespace Kaa\Component\Database;

interface EntityInterface
{
    /**
     * @internal
     *
     * @return string[]
     */
    public function getColumnNames(): array;

    /**
     * @internal
     */
    public function hydrate(mixed $values): void;
}
