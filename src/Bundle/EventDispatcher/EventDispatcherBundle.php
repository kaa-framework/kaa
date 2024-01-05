<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher;

use Exception;
use Kaa\Bundle\EventDispatcher\Writer\BootstrapWriter;
use Kaa\Bundle\EventDispatcher\Writer\ListenerWriter;
use Kaa\Bundle\Framework\BundleGeneratorInterface;
use Kaa\Component\EventDispatcher\EventDispatcher;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[PhpOnly]
class EventDispatcherBundle implements BundleGeneratorInterface
{
    /** @var mixed[] */
    private array $listeners = [];

    /**
     * @param mixed[] $config
     * @throws Exception
     */
    public function generate(SharedConfig $sharedConfig, array $config): void
    {
        $listeners = (new ListenerFinder($config))->getListeners();
        (new ListenerWriter($sharedConfig, $listeners))->write();
        (new BootstrapWriter($sharedConfig, $listeners))->write();

        $this->listeners = $listeners;
    }

    public function getRootConfigurationKey(): string
    {
        return 'dispatcher';
    }

    public function getConfiguration(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('router');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->arrayNode('scan')
            ->isRequired()
            ->prototype('scalar')
            ->end()
            ->arrayNode('listeners')
            ->arrayPrototype()
            ->children()
            ->scalarNode('service')
            ->isRequired()
            ->end()
            ->scalarNode('service_class')->end()
            ->scalarNode('method')
            ->defaultValue('invoke')
            ->end()
            ->scalarNode('event')
            ->isRequired()
            ->end()
            ->scalarNode('dispatcher')
            ->defaultValue('kernel')
            ->end()
            ->integerNode('priority')
            ->defaultValue(0)
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    public function getPriority(): int
    {
        return 20;
    }

    public function getConfigArray(): mixed
    {
        $dispatchers = array_column($this->listeners, 'dispatcher');
        $dispatchers[] = 'kernel';

        $dispatchers = array_unique($dispatchers);
        $dispatchers = array_map(
            static fn (string $dispatcher) => 'kernel.dispatcher.' . $dispatcher,
            $dispatchers,
        );

        $services = [];
        foreach ($dispatchers as $dispatcher) {
            $services[] = [
                $dispatcher => [
                    'class' => EventDispatcher::class,
                ]
            ];
        }

        return [
            'di' => [
                'services' => $services,
            ]
        ];
    }
}