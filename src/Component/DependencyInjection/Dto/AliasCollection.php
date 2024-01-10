<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class AliasCollection
{
    /**
     * Ключ - имя сервиса, значение - его алиасы
     * @var array<string, string[]>
     */
    private array $serviceToAliases = [];

    /**
     * Ключ - алиас, значение - имя сервиса
     * @var array<string, string>
     */
    private array $aliasToService = [];

    public function addAlias(string $serviceName, string $alias): self
    {
        if (!array_key_exists($serviceName, $this->serviceToAliases)) {
            $this->serviceToAliases[$serviceName] = [];
        }

        $this->serviceToAliases[$serviceName][] = $alias;

        $this->aliasToService[$alias] = $serviceName;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getServiceAliases(string $serviceName): array
    {
        return $this->serviceToAliases[$serviceName] ?? [];
    }

    public function getServiceName(string $alias): ?string
    {
        return $this->aliasToService[$alias] ?? null;
    }
}
