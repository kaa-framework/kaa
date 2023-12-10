<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Dto;

use ArrayIterator;
use IteratorAggregate;
use Kaa\Component\GeneratorContract\PhpOnly;
use Traversable;

#[PhpOnly]
class RoutesCollection implements IteratorAggregate
{
    /** @var RouteDto[] */
    private array $data = [];

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config)
    {
        foreach ($config['routes'] as $route) {
            $className = is_array($route['service'] ?? []) ? $route['service']['class'] : $route['service'];
            $name = is_array($route['service'] ?? []) ? $route['service']['class'] : $route['service'] . $route['method'];
            $routePath = array_key_exists($className, $config['prefixes'] ?? []) ?
                str_replace('//', '/', $config['prefixes'][$className] . $route['route']) :
                $route['route'];
            $this->data[] = new RouteDto(
                $routePath,
                $route['method'],
                $name,
                $className,
                $route['classMethod'],
            );
        }
    }

    public function has(string $route, ?string $method = null): bool
    {
        foreach ($this->data as $callableRoute) {
            if ($callableRoute->route === $route && ($method === null || $callableRoute->method === $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Traversable<RouteDto>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
