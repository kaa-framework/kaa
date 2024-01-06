<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\ValidatorLocator;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use ReflectionAttribute;
use ReflectionClass;

#[PhpOnly]
readonly class ModelLocator
{
    public function __construct(
        /** @var mixed[] */
        private array $config,
    ) {
    }

    /**
     * @return ReflectionClass[]
     *
     * @throws Exception
     */
    public function locate(): array
    {
        $validatedClasses = $this->findValidatedClasses();

        return array_values(array_filter($validatedClasses, $this->doesClassNeedValidation(...)));
    }

    /**
     * @return ReflectionClass[]
     * @throws Exception
     */
    private function findValidatedClasses(): array
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

        return array_map(
            static fn (string $class) => new ReflectionClass($class),
            $classes,
        );
    }

    private function doesClassNeedValidation(ReflectionClass $class): bool
    {
        foreach ($class->getProperties() as $reflectionProperty) {
            $assertAttributes = $reflectionProperty->getAttributes(
                AssertInterface::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if ($assertAttributes !== []) {
                return true;
            }
        }

        return false;
    }
}
