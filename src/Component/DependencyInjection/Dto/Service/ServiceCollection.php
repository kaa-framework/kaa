<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto\Service;

use ArrayIterator;
use IteratorAggregate;
use Kaa\Component\DependencyInjection\Exception\ServiceAlreadyExistsException;
use Kaa\Component\DependencyInjection\Exception\ServiceDoesNotExistException;
use Kaa\Component\DependencyInjection\Util\ClassParents;
use Kaa\Component\Generator\PhpOnly;
use Traversable;

#[PhpOnly]
class ServiceCollection implements IteratorAggregate
{
    /**
     * Ключ - класс или интерфейс, значение - массив сервисов, которые его реализуют
     * @var array<string, Service[]>
     */
    private array $classesToServices = [];

    /**
     * Ключ - название сервиса, значение - сервис
     * @var array<string, Service>
     */
    private array $services = [];

    /**
     * @return array<string, Service[]>
     */
    public function getClassesToServices(): array
    {
        return $this->classesToServices;
    }

    public function has(string $serviceName): bool
    {
        return array_key_exists($serviceName, $this->services);
    }

    /**
     * @throws ServiceDoesNotExistException
     */
    public function get(string $serviceName): Service
    {
        return $this->services[$serviceName] ?? throw new ServiceDoesNotExistException($serviceName);
    }

    /**
     * @throws ServiceAlreadyExistsException
     */
    public function add(Service $service): self
    {
        if (array_key_exists($service->name, $this->services)) {
            throw new ServiceAlreadyExistsException($service->name);
        }

        $this->services[$service->name] = $service;

        foreach (ClassParents::getClassParents($service->class) as $class) {
            if (!array_key_exists($class, $this->classesToServices)) {
                $this->classesToServices[$class] = [];
            }

            $this->classesToServices[$class][] = $service;
        }

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->services);
    }
}
