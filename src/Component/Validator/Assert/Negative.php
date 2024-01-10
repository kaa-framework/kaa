<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\NegativeGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Negative implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be negative.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float'];
    }

    /**
     * @return NegativeGenerator
     */
    public function getGenerator(): object
    {
        return new NegativeGenerator();
    }
}
