<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Assert;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Generator\AssertGeneratorInterface;

#[PhpOnly]
interface AssertInterface
{
    /**
     * @return AssertGeneratorInterface;
     */
    public function getGenerator(): object;

    /**
     * @return string[]
     */
    public function getAllowedTypes(): array;
}
