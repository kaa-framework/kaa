<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\NotNullGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class NotNull implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should not be null.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'float', 'bool', 'string', 'array'];
    }

    /**
     * @return NotNullGenerator
     */
    public function getGenerator(): object
    {
        return new NotNullGenerator();
    }
}
