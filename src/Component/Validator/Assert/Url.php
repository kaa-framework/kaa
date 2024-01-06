<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Attribute;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\Validator\Generator\UrlGenerator;

#[
    Attribute(Attribute::TARGET_PROPERTY),
    PhpOnly,
]
readonly class Url implements AssertInterface
{
    /**
     * @param string[] $protocols
     */
    public function __construct(
        public array $protocols = ['http', 'https'],
        public bool $relativeProtocol = false,
        public string $message = 'This value is not a valid URL.',
    ) {
    }

    public function getAllowedTypes(): array
    {
        return ['string'];
    }

    /**
     * @return UrlGenerator
     */
    public function getGenerator(): object
    {
        return new UrlGenerator();
    }
}
