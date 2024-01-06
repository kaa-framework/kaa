<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\IsTrueGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class IsTrue implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be true.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['bool'];
    }

    /**
     * @return IsTrueGenerator
     */
    public function getGenerator(): object
    {
        return new IsTrueGenerator();
    }
}
