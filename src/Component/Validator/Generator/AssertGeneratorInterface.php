<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Generator;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Validator\Assert\AssertInterface;
use ReflectionProperty;
use Twig;
use Twig\Environment;

#[PhpOnly]
interface AssertGeneratorInterface
{
    /**
     * @param Environment $twig
     */
    public function generateAssert(
        AssertInterface $assert,
        ReflectionProperty $reflectionProperty,
        string $className,
        Twig\Environment $twig,
    ): string;
}
