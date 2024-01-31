<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\AssertGeneratorInterface;
use Kaa\Component\Validator\Generator\ChoiceGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Choice implements AssertInterface
{
    public function __construct(
        /** @var array<int|float|string> */
        public array $choices,
        public bool $strict = true,
        public string $message = 'The value you selected is not a valid choice.',
        public bool $allowNull = false,
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['int', 'string', 'float'];
    }

    /**
     * @return ChoiceGenerator
     */
    public function getGenerator(): AssertGeneratorInterface
    {
        return new ChoiceGenerator();
    }
}
