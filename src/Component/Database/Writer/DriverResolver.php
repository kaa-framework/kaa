<?php

namespace Kaa\Component\Database\Writer;

use Kaa\Component\Database\Dto\EntityMetadata;
use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Database\Writer\EntityManagerWriter\EntityManagerWriterInterface;
use Kaa\Component\Database\Writer\EntityManagerWriter\PdoMysqlEntityManagerWriter;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;

#[PhpOnly]
final class DriverResolver
{
    /**
     * @param EntityMetadata[] $entityMetadata
     * @throws DatabaseGeneratorException
     */
    public static function getEntityManagerWriter(
        string $driver,
        string $connectionName,
        array $entityMetadata,
        SharedConfig $config,
    ): EntityManagerWriterInterface {
        return match ($driver) {
            'pdo_mysql' => new PdoMysqlEntityManagerWriter($connectionName, $entityMetadata, $config),
            default => throw new DatabaseGeneratorException("Driver {$driver} is not supported"),
        };
    }
}
