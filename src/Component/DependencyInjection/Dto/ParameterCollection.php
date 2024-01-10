<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto;

use Kaa\Component\DependencyInjection\Exception\ParameterDoesNotExistException;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
class ParameterCollection
{
    /** @var array<string, mixed> */
    private array $parameters = [];

    public function set(string $name, mixed $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * @throws ParameterDoesNotExistException
     */
    public function get(string $name): mixed
    {
        return $this->parameters[$name] ?? throw new ParameterDoesNotExistException($name);
    }
}
