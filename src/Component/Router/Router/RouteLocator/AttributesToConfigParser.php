<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Router\RouteLocator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\ClassFinder;
use Kaa\Component\Router\Attribute\Route;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

#[PhpOnly]
class AttributesToConfigParser
{
    public function __construct(
        /** @var mixed[] */
        private array $config,
    ) {
    }

    /**
     * @return mixed[]
     * @throws RouterGeneratorException
     */
    public function getConfig(): array
    {
        $handlerClasses = ClassFinder::find(
            scan: $this->config['scan'],
            predicate: static fn (ReflectionClass $c) => $c->isInstantiable() !== false,
        );

        foreach ($handlerClasses as $handlerClass) {
            $this->parseHandler($handlerClass);
        }

        return $this->config;
    }

    /**
     * @throws RouterGeneratorException
     */
    private function parseHandler(ReflectionClass $handlerClass): void
    {
        $routeAttributes = $handlerClass->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($routeAttributes) > 1) {
            throw new RouterGeneratorException(
                "{$handlerClass->getName()} should have only one 'Route' attribute",
            );
        }

        foreach ($routeAttributes as $handlerAttribute) {
            if ($handlerAttribute->newInstance()->method !== null) {
                throw new RouterGeneratorException(
                    "'Route' attribute should have 'method' argument as null while using for {$handlerClass->getName()}",
                );
            }
        }

        if ($routeAttributes !== []) {
            if (!array_key_exists($handlerClass->getName(), $this->config['prefixes'])) {
                $this->config['prefixes'][$handlerClass->getName()] = $routeAttributes[0]->newInstance()->route;
            } else {
                $this->config['prefixes'][$handlerClass->getName()] .= $routeAttributes[0]->newInstance()->route;
            }
        }

        $handlerMethods = $handlerClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($handlerMethods as $handlerMethod) {
            $this->addHandlerToConfig($handlerClass, $handlerMethod);
        }
    }

    private function addHandlerToConfig(ReflectionClass $handlerClass, ReflectionMethod $handlerMethod): void
    {
        foreach ($handlerMethod->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF) as $handlerAttribute) {
            $attributeInstance = $handlerAttribute->newInstance();
            $this->config['routes'][] = [
                'route' => $attributeInstance->route,
                'method' => $handlerAttribute->newInstance()->method,
                'service' => $handlerClass->getName(),
                'classMethod' => $handlerMethod->getName(),
            ];
        }
    }
}
