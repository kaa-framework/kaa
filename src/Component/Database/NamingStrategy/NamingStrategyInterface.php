<?php

namespace Kaa\Component\Database\NamingStrategy;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
interface NamingStrategyInterface
{
    public function getTableName(string $entityClass): string;

    public function getColumnName(string $fieldName): string;
}
