<?php

namespace Kaa\Component\Database;

use Exception;
use Kaa\Component\Database\Finder\EntityFinder;
use Kaa\Component\Database\Writer\DriverResolver;
use Kaa\Component\Database\Writer\EntityWriter;
use Kaa\Component\Generator\GeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;

#[PhpOnly]
class DatabaseGenerator implements GeneratorInterface
{
    /**
     * @throws Exception
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        foreach ($config as $name => $connectionConfig) {
            $entityFinder = new EntityFinder(
                $connectionConfig['scan'],
                $connectionConfig['naming_strategy'] ?? null,
            );

            $entityMetadata = $entityFinder->getMetadata();
            foreach ($entityMetadata as $metadata) {
                (new EntityWriter($name, $metadata, $sharedConfig))->write();
            }

            $entityManagerWriter = DriverResolver::getEntityManagerWriter(
                driver: $connectionConfig['driver']['type'],
                connectionName: $name,
                entityMetadata: $entityMetadata,
                config: $sharedConfig,
            );

            $entityManagerWriter->write();
        }
    }
}
