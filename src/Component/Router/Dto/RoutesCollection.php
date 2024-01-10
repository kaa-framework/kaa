<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Dto;

use ArrayIterator;
use IteratorAggregate;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Router\Decorator\DecoratorWriter;
use Traversable;

#[PhpOnly]
class RoutesCollection implements IteratorAggregate
{
    /** @var RouteDto[] */
    private array $data = [];

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config, DecoratorWriter $decoratorWriter)
    {
        foreach ($config['routes'] as $route) {
            $className = is_array($route['service'] ?? []) ? $route['service']['class'] : $route['service'];
            $serviceName = is_array($route['service'] ?? []) ? $route['service']['name'] : $route['service'];

            $name = is_array($route['service'] ?? []) ? $route['service']['class'] : $route['service'] . $route['method'];
            $routePath = array_key_exists($className, $config['prefixes'] ?? []) ?
                str_replace('//', '/', $config['prefixes'][$className] . $route['route']) :
                $route['route'];

            [$class, $methodName] = $decoratorWriter->addMethod($className, $serviceName, $route['classMethod']);

            $this->data[] = new RouteDto(
                route: $routePath,
                method: $route['method'],
                name: $name,
                className: $class,
                methodName: $methodName,
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
