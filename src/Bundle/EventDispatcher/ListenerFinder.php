<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\Bundle\EventDispatcher\Attribute\EventListener;
use Kaa\Component\GeneratorContract\PhpOnly;
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
        $classes = $this->findListenerClasses();

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

    /**
     * @return ReflectionClass[]
     * @throws Exception
     */
    private function findListenerClasses(): array
    {
        ClassFinder::disablePSR4Vendors();

        $classes = [];
        foreach ($this->scanFiles as $namespaceOrClass) {
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
            static fn (ReflectionClass $c) => $c->isInstantiable() && $c->getAttributes(EventListener::class) !== [],
        );
    }
}
