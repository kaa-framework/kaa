<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\PositiveOrZeroGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class PositiveOrZero implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be positive or zero.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return PositiveOrZeroGenerator
     */
    public function getGenerator(): object
    {
        return new PositiveOrZeroGenerator();
    }
}
