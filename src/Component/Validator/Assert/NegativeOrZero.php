<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\NegativeOrZeroGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class NegativeOrZero implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be negative or zero.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return NegativeOrZeroGenerator
     */
    public function getGenerator(): object
    {
        return new NegativeOrZeroGenerator();
    }
}
