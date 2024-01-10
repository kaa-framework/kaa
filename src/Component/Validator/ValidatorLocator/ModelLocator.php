<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\ValidatorLocator;

use Exception;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\Util\ClassFinder;
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
        return ClassFinder::find(
            scan: $this->config['scan'],
            predicate: $this->doesClassNeedValidation(...),
        );
    }

    private function doesClassNeedValidation(ReflectionClass $class): bool
    {
        foreach ($class->getProperties() as $reflectionProperty) {
            $assertAttributes = $reflectionProperty->getAttributes(
                AssertInterface::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if ($assertAttributes !== []) {
                return true;
            }
        }

        return false;
    }
}
