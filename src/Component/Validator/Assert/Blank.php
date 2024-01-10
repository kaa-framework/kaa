<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\AssertGeneratorInterface;
use Kaa\Component\Validator\Generator\BlankGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Blank implements AssertInterface
{
    public function __construct(
        public string $message = 'This value should be blank.',
        public bool $allowNull = false,
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['string'];
    }

    /**
     * @return BlankGenerator
     */
    public function getGenerator(): AssertGeneratorInterface
    {
        return new BlankGenerator();
    }
}
