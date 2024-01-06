<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\GreaterThanOrEqualGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class GreaterThanOrEqual implements AssertInterface
{
    public function __construct(
        public int|float $value,
        public string $message = 'This value should be greater than or equal to {{ compared_value }}.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return GreaterThanOrEqualGenerator
     */
    public function getGenerator(): object
    {
        return new GreaterThanOrEqualGenerator();
    }
}
