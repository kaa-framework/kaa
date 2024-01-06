<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\LessThanGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class LessThan implements AssertInterface
{
    public function __construct(
        public int|float $value,
        public string $message = 'This value should be less than {{ compared_value }}.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return LessThanGenerator
     */
    public function getGenerator(): object
    {
        return new LessThanGenerator();
    }
}
