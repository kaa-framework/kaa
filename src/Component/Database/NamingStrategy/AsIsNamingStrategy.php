<?php

namespace Kaa\Component\Database\NamingStrategy;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class AsIsNamingStrategy implements NamingStrategyInterface
{
    public function getTableName(string $entityClass): string
    {
        $parts = explode('\\', $entityClass);

        return end($parts);
    }

    public function getColumnName(string $fieldName): string
    {
        return $fieldName;
    }
}
