<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher;

use Exception;
use Kaa\Bundle\EventDispatcher\Attribute\EventListener;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\ClassFinder;
use ReflectionClass;

#[PhpOnly]
readonly class ListenerFinder
{
    /** @var mixed[] */
    public array $listeners;

    /** @var string[] */
    public array $scanFiles;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config)
    {
        $this->listeners = $config['listeners'];
        $this->scanFiles = $config['scan'];
    }

    /**
     * @return mixed[]
     * @throws Exception
     */
    public function getListeners(): array
    {
        $classes = ClassFinder::find(
            $this->scanFiles,
            predicate: static fn (ReflectionClass $c) => $c->isInstantiable()
                && $c->getAttributes(EventListener::class) !== [],
        );

        $listeners = $this->listeners;
        foreach ($classes as $class) {
            foreach ($class->getAttributes(EventListener::class) as $attr) {
                /** @var EventListener $attribute */
                $attribute = $attr->newInstance();

                $listeners[] = [
                    'service' => $class->getName(),
                    'method' => $attribute->method,
                    'event' => $attribute->event,
                    'priority' => $attribute->priority,
                    'dispatcher' => $attribute->dispatcher,
                ];
            }
        }

        return $listeners;
    }
}
