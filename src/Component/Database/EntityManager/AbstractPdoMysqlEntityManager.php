<?php

namespace Kaa\Component\Database\EntityManager;

use Kaa\Component\Database\EntityInterface;
use PDO;

abstract class AbstractPdoMysqlEntityManager extends AbstractEntityManager
{
    protected PDO $pdo;

    public function __construct(
        string $host,
        string $database,
        string $user,
        string $password
    ) {
        $this->pdo = new PDO("mysql:host={$host};dbname={$database}", $user, $password);
    }

    /**
     * @param EntityInterface[] $newEntities
     */
    protected function insert(array $newEntities): void
    {
        foreach ($this->newEntities as $entity) {
            if ($entity->_getId() === null) {
                $sql = sprintf(
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $entity->_getTableName(),
                    implode(', ', $entity->_getColumnNames()),
                    implode(', ', $entity->_getValues()),
                );

                $this->pdo->exec($sql);
                $entity->_setId((int) $this->pdo->query('SELECT LAST_INSERT_ID();')->fetch()[0]);
            } else {
                $sql = sprintf(
                    'INSERT INTO %s (%s %s) VALUES (%s %s)',
                    $entity->_getTableName(),
                    $entity->_getIdColumnName(),
                    $entity->_getColumnNames() !== [] ? ',' . implode(', ', $entity->_getColumnNames()) : '',
                    $entity->_getId(),
                    $entity->_getColumnNames() !== [] ? ',' . implode(', ', $entity->_getValues()) : '',
                );

                $this->pdo->exec($sql);
            }
        }
    }

    protected function update(): void
    {
        foreach ($this->managedEntities as $entityWithValueSet) {
            $entity = $entityWithValueSet->getEntity();
            if (!$entity->_isInitialized()) {
                continue;
            }

            $changes = $this->getChangeset($entity->_getValues(), $entityWithValueSet->getValues());
            if ($changes === []) {
                continue;
            }

            $sql = "UPDATE {$entity->_getTableName()} SET ";
            foreach ($changes as $column => $value) {
                $sql .= "{$column} = {$value}, ";
            }

            $sql = rtrim($sql, ', ');

            $sql .= " WHERE {$entity->_getIdColumnName()} = {$entity->_getId()}";

            $this->pdo->exec($sql);
            $entityWithValueSet->setValues($entity->_getValues());
        }
    }

    public function _getPdo(): PDO
    {
        return $this->pdo;
    }
}
