<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\ServiceLocator;

use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\Argument;
use Kaa\Component\DependencyInjection\Dto\Service\ArgumentType;
use Kaa\Component\DependencyInjection\Dto\Service\ConstructionType;
use Kaa\Component\DependencyInjection\Dto\Service\Factory;
use Kaa\Component\DependencyInjection\Dto\Service\Service;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\DependencyInjection\Exception\ServiceAlreadyExistsException;
use Kaa\Component\Generator\Exception\BadTypeException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\Reflection;
use ReflectionClass;

#[PhpOnly]
readonly class ConfigServiceLocator
{
    public function __construct(
        /** @var mixed[] */
        private array $config,
        private ServiceCollection $serviceCollection,
        private ParameterCollection $parameterCollection,
        private AliasCollection $aliasCollection,
    ) {
    }

    /**
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException
     */
    public function locate(): void
    {
        foreach ($this->config['parameters'] ?? [] as $name => $value) {
            $this->parameterCollection->set($name, $value);
        }

        foreach ($this->config['aliases'] ?? [] as $alias => $service) {
            $this->aliasCollection->addAlias($service, $alias);
        }

        foreach ($this->config['services'] ?? [] as $serviceName => $serviceData) {
            $this->parseService($serviceName, $serviceData);
        }
    }

    /**
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException|BadTypeException
     */
    private function parseService(string $serviceName, mixed $serviceData): void
    {
        if (!array_key_exists('class', $serviceData) && !class_exists($serviceName)) {
            throw new InvalidServiceDefinitionException("You must specify class for service {$serviceName}");
        }

        $class = $serviceData['class'] ?? $serviceName;
        if (!class_exists($class)) {
            throw new InvalidServiceDefinitionException("Invalid class {$class} for service {$serviceName}");
        }

        $bothArgumentsAndFactoryAreDefined =
            array_key_exists('arguments', $serviceData) && $serviceData['arguments'] !== []
            && array_key_exists('factory', $serviceData) && $serviceData['factory'] !== [];

        if ($bothArgumentsAndFactoryAreDefined) {
            throw new InvalidServiceDefinitionException(
                "You cannot set both arguments and factory for service {$serviceName}"
            );
        }

        $reflectionClass = new ReflectionClass($class);
        if (array_key_exists('factory', $serviceData) && $serviceData['factory'] !== []) {
            $arguments = null;

            $factoryData = $serviceData['factory'];
            $factory = new Factory(
                $factoryData['service'] ?? throw new InvalidServiceDefinitionException(
                    "Service must be specified in factory for service {$serviceName}"
                ),
                $factoryData['method'] ?? 'invoke',
                $factoryData['static'] ?? false,
            );
        } else {
            $arguments = $this->getArguments($reflectionClass, $serviceData['arguments'] ?? []);
            $factory = null;
        }

        $service = new Service(
            $serviceName,
            $reflectionClass,
            $serviceData['singleton'] ?? true,
            $factory !== null ? ConstructionType::Factory : ConstructionType::Constructor,
            $arguments,
            $factory,
        );

        $this->serviceCollection->add($service);
    }

    /**
     * @return Argument[]
     * @throws InvalidServiceDefinitionException|BadTypeException
     */
    private function getArguments(ReflectionClass $reflectionClass, mixed $argumentsData): array
    {
        if (!$reflectionClass->hasMethod('__construct')) {
            return [];
        }

        $arguments = [];
        $constructorArguments = $reflectionClass->getMethod('__construct')->getParameters();
        foreach ($constructorArguments as $argument) {
            if (array_key_exists($argument->getName(), $argumentsData)) {
                $argumentValue = $argumentsData[$argument->getName()];

                $arguments[] = match (true) {
                    str_starts_with($argumentValue, '@') => new Argument(
                        ArgumentType::Service,
                        substr($argumentValue, 1)
                    ),
                    str_starts_with($argumentValue, '%') => new Argument(
                        ArgumentType::Parameter,
                        substr($argumentValue, 1)
                    ),
                    default => throw new InvalidServiceDefinitionException(
                        "{$argument->getName()} of class {$reflectionClass->getName()} must be either a service or parameter, {$argumentValue} given"
                    ),
                };

                continue;
            }

            $arguments[] = new Argument(ArgumentType::Service, Reflection::namedType($argument->getType())->getName());
        }

        return $arguments;
    }
}
