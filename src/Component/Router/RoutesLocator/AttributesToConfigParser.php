<?php

declare(strict_types=1);

namespace Kaa\Component\Router\RoutesLocator;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Router\Attribute\Route;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
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
     * @throws ReflectionException|RouterGeneratorException
     */
    public function getConfig(): array
    {
        $handlerClasses = $this->findHandlerClasses();
        foreach ($handlerClasses as $handlerClass) {
            $this->parseHandler($handlerClass);
        }

        return $this->config;
    }

    /**
     * @return ReflectionClass[]
     * @throws ReflectionException|Exception
     */
    private function findHandlerClasses(): array
    {
        ClassFinder::disablePSR4Vendors();

        $classes = [];
        foreach ($this->config['scan'] as $namespaceOrClass) {
            $namespaceOrClass = trim($namespaceOrClass, '\\');
            if (class_exists($namespaceOrClass)) {
                $classes[] = [$namespaceOrClass];
            }

            $classes[] = ClassFinder::getClassesInNamespace($namespaceOrClass, ClassFinder::RECURSIVE_MODE);
        }
        $classes = array_merge(...$classes);
        $reflectionClasses = array_map(
            static fn (string $class) => new ReflectionClass($class),
            $classes,
        );

        return array_filter(
            $reflectionClasses,
            static fn (ReflectionClass $c) => $c->isInstantiable() !== false,
        );
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
            if ($this->config['prefixes'][$handlerClass->getName()] === []) {
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
