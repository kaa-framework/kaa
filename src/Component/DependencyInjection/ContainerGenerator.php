<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection;

use Exception;
use Kaa\Component\DependencyInjection\Dto\AliasCollection;
use Kaa\Component\DependencyInjection\Dto\ParameterCollection;
use Kaa\Component\DependencyInjection\Dto\Service\ServiceCollection;
use Kaa\Component\DependencyInjection\Exception\InvalidServiceDefinitionException;
use Kaa\Component\DependencyInjection\Exception\ServiceAlreadyExistsException;
use Kaa\Component\DependencyInjection\ServiceLocator\AttributesToConfigParser;
use Kaa\Component\DependencyInjection\ServiceLocator\ConfigServiceLocator;
use Kaa\Component\Generator\GeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;

#[PhpOnly]
readonly class ContainerGenerator implements GeneratorInterface
{
    /**
     * @param mixed[] $config
     * @throws InvalidServiceDefinitionException|ServiceAlreadyExistsException|Exception
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        $parameterCollection = new ParameterCollection();
        $aliasCollection = new AliasCollection();
        $serviceCollection = new ServiceCollection();

        $config = (new AttributesToConfigParser($config))->getConfig();
        (new ConfigServiceLocator($config, $serviceCollection, $parameterCollection, $aliasCollection))->locate();
        (new ContainerWriter($sharedConfig, $serviceCollection, $parameterCollection, $aliasCollection))->write();
    }
}
