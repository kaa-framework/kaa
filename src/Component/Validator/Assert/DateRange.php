<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use DateTimeInterface;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\AssertGeneratorInterface;
use Kaa\Component\Validator\Generator\DateRangeGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class DateRange implements AssertInterface
{
    public function __construct(
        public string $format,
        public ?DateTimeInterface $before = null,
        public ?DateTimeInterface $after = null,
        public string $message_after = 'This value should be after {{ date }}.',
        public string $message_before = 'This value should be before {{ date }}.',
        public string $message_between = 'This value should be between {{ after }} and {{ before }}.',
        public bool $allowNull = false,
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['string'];
    }

    /**
     * @return DateRangeGenerator
     */
    public function getGenerator(): AssertGeneratorInterface
    {
        return new DateRangeGenerator();
    }
}
