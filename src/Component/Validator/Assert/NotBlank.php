<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\NotBlankGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class NotBlank implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should not be blank.',
        public bool $allowNull = false,
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['string'];
    }

    /**
     * @return NotBlankGenerator
     */
    public function getGenerator(): object
    {
        return new NotBlankGenerator();
    }
}
