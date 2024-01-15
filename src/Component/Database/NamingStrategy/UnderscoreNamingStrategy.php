<?php

namespace Kaa\Component\Database\NamingStrategy;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class UnderscoreNamingStrategy implements NamingStrategyInterface
{
    public function getTableName(string $entityClass): string
    {
        $parts = explode('\\', $entityClass);

        return $this->camelCaseToSnakeCase(end($parts));
    }

    public function getColumnName(string $fieldName): string
    {
        return $this->camelCaseToSnakeCase($fieldName);
    }

    private function camelCaseToSnakeCase(string $string): string
    {
        return strtolower(
            preg_replace(
                '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/',
                '_',
                $string,
            )
        );
    }
}
