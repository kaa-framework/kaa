<?php

namespace Kaa\Component\Database;

interface EntityInterface
{
    /**
     * @internal
     *
     * @return string[]
     */
    public function _getColumnNames(): array;

    /**
     * @internal
     */
    public function _hydrate(mixed $values): void;

    public function _getValues(): mixed;

    public function _getId(): ?int;

    public function _setId(int $id): void;

    public function _getIdColumnName(): string;

    public function _getTableName(): string;
}
