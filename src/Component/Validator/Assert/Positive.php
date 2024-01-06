<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\PositiveGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Positive implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be positive.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return PositiveGenerator
     */
    public function getGenerator(): object
    {
        return new PositiveGenerator();
    }
}
