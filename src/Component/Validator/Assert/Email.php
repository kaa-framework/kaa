<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\EmailGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Email implements AssertInterface
{
    public function __construct(
        public string $mode = 'loose',
        public string $message = 'This value is not a valid email address.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['string'];
    }

    /**
     * @return EmailGenerator
     */
    public function getGenerator(): object
    {
        return new EmailGenerator();
    }
}
