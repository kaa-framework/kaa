<?php

declare(strict_types=1);

namespace Kaa\Component\Router;

use Exception;
use Kaa\Component\Generator\GeneratorInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Router\Decorator\DecoratorWriter;
use Kaa\Component\Router\Dto\RoutesCollection;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Component\Router\RoutesLocator\AttributesToConfigParser;
use Kaa\Component\Router\RoutesLocator\ConfigValidator;
use Kaa\Component\Router\RoutingTree\RoutingTree;
use ReflectionException;

#[PhpOnly]
readonly class RouterGenerator implements GeneratorInterface
{
    /**
     * @param mixed[] $config
     * @throws ReflectionException|RouterGeneratorException|Exception
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        $tree = new RoutingTree();
        $config = (new AttributesToConfigParser($config))->getConfig();
        ConfigValidator::validate($config);

        $decoratorWriter = new DecoratorWriter($sharedConfig);

        $routesCollection = new RoutesCollection($config, $decoratorWriter);
        foreach ($routesCollection as $route) {
            $tree->addElement($route);
        }

        (new RouterWriter($sharedConfig, $tree))->write();
        $decoratorWriter->write();
    }
}
