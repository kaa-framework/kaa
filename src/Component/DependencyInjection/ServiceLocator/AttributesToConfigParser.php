<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\ServiceLocator;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\Component\DependencyInjection\Attribute\Autowire;
use Kaa\Component\DependencyInjection\Attribute\Factory as FactoryAttribute;
use Kaa\Component\DependencyInjection\Attribute\Service as ServiceAttribute;
use Kaa\Component\DependencyInjection\Exception\DependencyInjectionGeneratorException;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\Generator\PhpOnly;
use ReflectionAttribute;
use ReflectionClass;

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
     *
     * @throws Exception
     */
    public function getConfig(): array
    {
        $serviceClasses = $this->findServiceClasses();
        foreach ($serviceClasses as $serviceClass) {
            $this->parseService($serviceClass);
        }

        return $this->config;
    }

    /**
     * @return ReflectionClass[]
     * @throws Exception
     */
    private function findServiceClasses(): array
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

        $reflectionClasses = array_filter($reflectionClasses, $this->notInIgnoredNamespace(...));

        return array_filter(
            $reflectionClasses,
            static fn (ReflectionClass $c) => $c->isInstantiable() || $c->getAttributes(FactoryAttribute::class) !== [],
        );
    }

    /**
     * @throws DependencyInjectionGeneratorException
     */
    private function notInIgnoredNamespace(ReflectionClass $class): bool
    {
        foreach ($this->config['ignore'] ?? [] as $ignore) {
            if ($class->getName() === $ignore) {
                return false;
            }
            $ignore = trim($ignore, '\\');
            // Одна звёздочка -> одно вложенное пространство имён
            $ignore = str_replace(['\\', '*'], ['\\\\', '[^\\]+'], $ignore);
            $ignore = '^' . $ignore . '.*';

            if (preg_match("/{$ignore}/", $class->getName(), $matches) === false) {
                throw new DependencyInjectionGeneratorException(
                    "Error while matching class name {$class->getName()} with pattern /{$ignore}/",
                );
            }

            if ($matches !== []) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws InvalidServiceDefinitionException
     */
    private function parseService(ReflectionClass $class): void
    {
        if (array_key_exists($class->getName(), $this->config)) {
            return;
        }

        $serviceAttributes = $class->getAttributes(ServiceAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        $serviceAttribute = $serviceAttributes !== []
            ? $serviceAttributes[0]->newInstance()
            : new ServiceAttribute();

        foreach ((array) $serviceAttribute->aliases as $alias) {
            if (array_key_exists($alias, $this->config['aliases'])) {
                throw new InvalidServiceDefinitionException(
                    "Class {$class->getName()} redefines alias '{$alias}' already set to '{$this->config['aliases'][$alias]}'",
                );
            }

            $this->config['aliases'][$alias] = $class->getName();
        }

        $factoryAttributes = $class->getAttributes(FactoryAttribute::class, ReflectionAttribute::IS_INSTANCEOF);

        /** @var FactoryAttribute|null $factoryAttribute */
        $factoryAttribute = $factoryAttributes !== [] ? $factoryAttributes[0]->newInstance() : null;

        $serviceData = [
            'singleton' => $serviceAttribute->singleton,
        ];

        if ($factoryAttribute !== null) {
            $serviceData['factory'] = [
                'service' => $factoryAttribute->service,
                'method' => $factoryAttribute->method,
                'static' => $factoryAttribute->isStatic,
            ];
        } else {
            $serviceData['arguments'] = $this->getArguments($class);
        }

        $this->config['services'][$class->getName()] = $serviceData;
    }

    /**
     * @return mixed[]
     * @throws InvalidServiceDefinitionException
     */
    private function getArguments(ReflectionClass $class): array
    {
        if (!$class->hasMethod('__construct')) {
            return [];
        }

        $arguments = [];
        $constructorArguments = $class->getMethod('__construct')->getParameters();
        foreach ($constructorArguments as $argument) {
            $autowireAttributes = $argument->getAttributes(Autowire::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($autowireAttributes === []) {
                continue;
            }

            /** @var Autowire $autowireAttribute */
            $autowireAttribute = $autowireAttributes[0]->newInstance();
            if (
                ($autowireAttribute->service === null && $autowireAttribute->parameter === null)
                || ($autowireAttribute->service !== null && $autowireAttribute->parameter !== null)
            ) {
                throw new InvalidServiceDefinitionException(
                    "Exactly one of service or parameter must be set in #[Autowire] for {$class->getName()}::{$argument->getName()}",
                );
            }

            $arguments[$argument->getName()] = $autowireAttribute->service !== null
                ? '@' . $autowireAttribute->service
                : '%' . $autowireAttribute->parameter;
        }

        return $arguments;
    }
}
