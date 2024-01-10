<?php

namespace Kaa\Component\RequestMapperDecorator;

use Attribute;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PARAMETER),
]
readonly class MapQueryParameters extends AbstractBagDecorator
{
    protected function getInputBagName(): string
    {
        return 'query';
    }
}
