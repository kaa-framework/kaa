<?php

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\LengthGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
class Length implements AssertInterface
{
    public function __construct(
        public ?int $exactly = null,
        public string $exactlyMessage = 'This value should have exactly {{ limit }} characters',

        public ?int $min = null,
        public string $minMessage = 'This value is too short. It should have {{ limit }} characters or more.',

        public ?int $max = null,
        public string $maxMessage = 'This value is too long. It should have {{ limit }} characters or less.',
    ) {
    }

    /**
     * @return LengthGenerator
     */
    public function getGenerator(): object
    {
        return new LengthGenerator();
    }

    public function getAllowedTypes(): array
    {
        return ['string'];
    }
}
