<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\AllGenerator;
use Kaa\Component\Validator\Generator\AssertGeneratorInterface;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class All implements AssertInterface
{
    /**
     * @param array<AssertInterface> $asserts
     */
    public function __construct(
        public array $asserts,
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['array'];
    }

    /**
     * @return AllGenerator
     */
    public function getGenerator(): AssertGeneratorInterface
    {
        return new AllGenerator();
    }
}
