<?php

declare(strict_types=1);

namespace Kaa\Component\DependencyInjection\Dto\Service;

use Kaa\Component\Generator\PhpOnly;
use ReflectionClass;

#[PhpOnly]
readonly class Service
{
    public function __construct(
        public string $name,
        public ReflectionClass $class,
        public bool $isSingleton,
        public ConstructionType $constructionType,

        /** @var Argument[]|null */
        public ?array $arguments = null,
        public ?Factory $factory = null,
    ) {
    }
}
