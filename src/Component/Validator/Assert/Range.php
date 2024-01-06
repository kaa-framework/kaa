<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\RangeGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Range implements AssertInterface
{
    public function __construct(
        public int|float $min,
        public int|float $max,
        public string $message = 'The value must lie in the range from {{ min }} to {{ max }}',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return RangeGenerator
     */
    public function getGenerator(): object
    {
        return new RangeGenerator();
    }
}
