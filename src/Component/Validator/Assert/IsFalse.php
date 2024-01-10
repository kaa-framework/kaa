<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\IsFalseGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class IsFalse implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be false.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['bool'];
    }

    /**
     * @return IsFalseGenerator
     */
    public function getGenerator(): object
    {
        return new IsFalseGenerator();
    }
}
